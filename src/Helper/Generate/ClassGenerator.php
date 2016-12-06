<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use Commercetools\Core\Helper\Generate\ArraySerializable;
use Commercetools\Core\Templates\Common\JsonObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpParser\BuilderFactory;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use ReflectionClass;
use ReflectionProperty;

class ClassGenerator
{
    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;
    protected $newDocBlock;
    protected $fields;
    protected $fieldNames;
    protected $includes;
    protected $uses;

    protected $path;
    protected $outputPath;
    protected $oldNamespace;
    protected $newNamespace;

    public function __construct($path, $outputPath, $oldNamespace, $newNamespace)
    {
        $this->path = $path;
        $this->outputPath = $outputPath;
        $this->oldNamespace = $oldNamespace;
        $this->newNamespace = $newNamespace;

        $annotationClasses = [
            JsonField::class,
            JsonFieldSetter::class,
            DiscriminatorColumn::class,
            CollectionType::class,
            Draftable::class,
            DraftableCollection::class,
            ReferenceType::class,
            CollectionSetter::class
        ];
        foreach ($annotationClasses as $annotationClass) {
            $class = new ReflectionClass($annotationClass);
            AnnotationRegistry::registerFile(
                $class->getFileName()
            );
        }
    }
    public function run()
    {
        $path = realpath($this->path);

        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');

        $path = realpath($this->path);
        $this->ensureDirExists($this->outputPath);
        $outputPath = realpath($this->outputPath);

        $files = [];
        foreach ($phpFiles as $file) {
            $files[] = $this->moveClassToNewNamespace(
                $file,
                $this->oldNamespace,
                $this->newNamespace,
                $path,
                $outputPath
            );
        }

        $this->generateBaseDrafts($this->getDraftableClasses($outputPath), $this->newNamespace);

        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($outputPath));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        $this->generateFiles($phpFiles, $this->newNamespace);
    }

    protected function generateFiles($files, $namespace)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);

        foreach ($files as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor(new JsonFieldGetterVisitor());
            $traverser->addVisitor(new JsonFieldSetterVisitor());
            $traverser->addVisitor(new CollectionVisitor());
            $traverser->addVisitor(new ReferenceVisitor());
            $traverser->addVisitor(new CollectionSetterVisitor());
            $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace));
            $traverser->traverse($stmts);


            $this->writeClass($file, $stmts);
        }
    }

    protected function moveClassToNewNamespace($file, $oldNamespace, $newNamespace, $inputPath, $outputPath)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $code = file_get_contents($file);
        $stmts = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(new NamespaceChangeVisitor($oldNamespace, $newNamespace));
        $traverser->traverse($stmts);

        $outputFile = str_replace($inputPath, $outputPath, $file);
        $this->writeClass($outputFile, $stmts);
        return $outputFile;
    }

    protected function getDraftableClasses($path)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');

        $draftVisitor = new DraftVisitor();
        foreach ($phpFiles as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor($draftVisitor);
            $traverser->traverse($stmts);
        }
        return $draftVisitor->getDraftableClasses();
    }

    protected function writeAnnotation($annotation)
    {
        $class = new ReflectionClass($annotation);
        $t = [];
        foreach (get_object_vars($annotation) as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value)) {
                $t[] = sprintf('%s={"%s"}', $key, implode('", "', $value));
            } elseif (is_numeric($value)) {
                $t[] = sprintf('%s=%s', $key, $value);
            } else {
                $t[] = sprintf('%s="%s"', $key, $value);
            }
        }

        return '@' . $class->getShortName() . '(' . implode(', ', $t) . ')';
    }

    public function generateBaseDrafts($draftableClasses, $namespace)
    {
        $reader = new AnnotationReader();
        $factory = new BuilderFactory();

        /**
         * @var \ReflectionClass $reflectedClass
         */
        $files = [];
        foreach ($draftableClasses as $reflectedClass) {
            $annotation = $reader->getClassAnnotation($reflectedClass, Draftable::class);

            if ($annotation instanceof Draftable) {
                $classAnnotations = array_filter(
                    $reader->getClassAnnotations($reflectedClass),
                    function ($annotation) {
                        return !($annotation instanceof Draftable);
                    }
                );

                $docComment = '/**' . PHP_EOL . ' *' . PHP_EOL;
                foreach ($classAnnotations as $classAnnotation) {
                    $docComment.= ' * ' . $this->writeAnnotation($classAnnotation) . PHP_EOL;
                }
                $docComment.=' */';

                $classBuilder = $factory->class($reflectedClass->getShortName() . 'Draft')
                    ->setDocComment($docComment)
                    ->extend($reflectedClass->getParentClass()->getShortName());

                list($types, $uses) = $this->getUses($reflectedClass);
                $builder = $factory->namespace($reflectedClass->getNamespaceName());

                $classUses = [
                    'JsonField' => $factory->use(JsonField::class),
                    'JsonFieldSetter' => $factory->use(JsonFieldSetter::class),
                    'ReferenceType' => $factory->use(ReferenceType::class),
                ];
                if ($reflectedClass->getNamespaceName() != $reflectedClass->getParentClass()->getNamespaceName()) {
                    $classUses[$reflectedClass->getParentClass()->getShortName()] = $factory->use(
                        $reflectedClass->getParentClass()->getName()
                    );
                }
                foreach ($this->getProperties($reflectedClass) as $property) {
                    if (!in_array($property->getName(), $annotation->fields)) {
                        continue;
                    }

                    $jsonFieldAnnotation = $reader->getPropertyAnnotation($property, JsonField::class);
                    $setter = new JsonFieldSetter();
                    if ($jsonFieldAnnotation instanceof JsonField) {
                        $setter->paramTypes = [$jsonFieldAnnotation->type];
                        if (isset($uses[$jsonFieldAnnotation->type])) {
                            $draftType = $uses[$jsonFieldAnnotation->type]['name'];
                        } else {
                            $draftType = $reflectedClass->getNamespaceName() . '\\' . $jsonFieldAnnotation->type;
                        }
                        if (isset($draftableClasses[$draftType])) {
                            $setter->paramTypes[] = $jsonFieldAnnotation->type . 'Draft';
                            $uses[$jsonFieldAnnotation->type . 'Draft'] = ['name' => $draftType . 'Draft'];
                        }
                        if (count($setter->paramTypes) == 1) {
                            $setter->type = current($setter->paramTypes);
                        }
                    }
                    $docComment = str_replace(
                        '/**',
                        '/**' . PHP_EOL . ' * ' . $this->writeAnnotation($setter),
                        $property->getDocComment()
                    );

                    $draftProperty = $factory
                        ->property($property->getName())
                        ->setDocComment($docComment);
                    if ($property->isProtected() || $property->isPrivate()) {
                        $draftProperty = $draftProperty->makeProtected();
                    }
                    $classBuilder->addStmt($draftProperty);

                    if (isset($types[$property->getName()])) {
                        $type = $types[$property->getName()];
                        if (isset($uses[$type])) {
                            $use = $uses[$type];
                            $node = $factory->use($use['name']);
                            if (isset($use['alias'])) {
                                $node->as($use['alias']);
                            }

                            $classUses[$type] = $node->getNode();
                        }
                        if (isset($uses[$type . 'Draft'])) {
                            $use = $uses[$type . 'Draft'];
                            $node = $factory->use($use['name']);
                            if (isset($use['alias'])) {
                                $node->as($use['alias']);
                            }

                            $classUses[$type . 'Draft'] = $node->getNode();
                        }
                    }
                }
                $builder->addStmts(array_values($classUses));
                $builder->addStmt($classBuilder);
                $node = $builder->getNode();
                $stmts = array($node);

                $traverser = new NodeTraverser();
                $traverser->addVisitor(new NameResolver()); // we will need resolved names
                $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace));
                $traverser->traverse($stmts);

                $fileName = str_replace(
                    $reflectedClass->getShortName(),
                    $reflectedClass->getShortName() . 'Draft',
                    $reflectedClass->getFileName()
                );
                $this->writeClass($fileName, $stmts);
                $files[] = $fileName;
            }

            $annotation = $reader->getClassAnnotation($reflectedClass, DraftableCollection::class);

            if ($annotation instanceof DraftableCollection) {
                list($types, $uses) = $this->getUses($reflectedClass);
                $classUses = [
                    'CollectionType' => $factory->use(CollectionType::class),
                    'CollectionSetter' => $factory->use(CollectionSetter::class),
                    'ArraySerializable' => $factory->use(ArraySerializable::class)
                ];
                foreach ($uses as $alias => $use) {
                    $node = $factory->use($use['name']);
                    if (isset($use['alias'])) {
                        $node->as($use['alias']);
                    }
                    $classUses[$alias] = $node->getNode();
                }

                $classAnnotations = $reader->getClassAnnotations($reflectedClass);
                $draftAnnotations = [];
                foreach ($classAnnotations as $classAnnotation) {
                    if ($classAnnotation instanceof DraftableCollection) {
                        $newAnnotation = new CollectionSetter();
                        $newAnnotation->type = $classAnnotation->type;
                        $classAnnotation = $newAnnotation;
                    }
                    $draftAnnotations[get_class($classAnnotation)] = $classAnnotation;
                }
                if (isset($draftAnnotations[CollectionType::class])) {
                    $collAnnotation = $draftAnnotations[CollectionType::class];

                    $draft = $draftAnnotations[CollectionSetter::class];
                    $draft->elementTypes[] = $collAnnotation->type;

                    if (isset($uses[$collAnnotation->type]['name']) &&
                        class_exists($uses[$collAnnotation->type]['name'] . 'Draft')
                    ) {
                        $class = $uses[$collAnnotation->type]['name'];
                        $draftClass = $class . 'Draft';
                        $collAnnotation->type = $collAnnotation->type . 'Draft';
                        $classUses[$collAnnotation->type] = $factory->use($draftClass);

                        $draft->elementTypes[] = $collAnnotation->type;
                    }
                }

                $docComment = '/**' . PHP_EOL;
                foreach ($draftAnnotations as $draftAnnotation) {
                    $docComment.= ' * ' . $this->writeAnnotation($draftAnnotation) . PHP_EOL;
                }
                $docComment.= ' */';

                $classBuilder = $factory->class($reflectedClass->getShortName() . 'Draft')
                    ->setDocComment($docComment)
                    ->extend($reflectedClass->getParentClass()->getShortName());

                $builder = $factory->namespace($reflectedClass->getNamespaceName());

                if ($reflectedClass->getNamespaceName() != $reflectedClass->getParentClass()->getNamespaceName()) {
                    $classUses[$reflectedClass->getParentClass()->getShortName()] = $factory->use(
                        $reflectedClass->getParentClass()->getName()
                    );
                }
                $builder->addStmts(array_values($classUses));
                $builder->addStmt($classBuilder);
                $node = $builder->getNode();
                $stmts = array($node);

                $traverser = new NodeTraverser();
                $traverser->addVisitor(new NameResolver()); // we will need resolved names
                $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace));
                $traverser->traverse($stmts);

                $fileName = str_replace(
                    $reflectedClass->getShortName(),
                    $reflectedClass->getShortName() . 'Draft',
                    $reflectedClass->getFileName()
                );
                $this->writeClass($fileName, $stmts);
                $files[] = $fileName;
            }
        }

        return $files;
    }

    protected function writeClass($filename, $stmts)
    {
        $printer = new PrettyPrinter\Standard();
        $this->ensureDirExists(dirname($filename));
        file_put_contents($filename, '<?php ' . PHP_EOL . $printer->prettyPrint($stmts));
    }

    protected function getUses(ReflectionClass $class)
    {
        $code = file_get_contents($class->getFileName());
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $stmts = $parser->parse($code);

        $useVisitor = new UseVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor($useVisitor);
        $traverser->traverse($stmts);

        return [$useVisitor->getPropertyTypes(), $useVisitor->getUses()];
    }

    /**
     * @param ReflectionClass $reflectedClass
     * @return ReflectionProperty[]
     */
    protected function getProperties(ReflectionClass $reflectedClass)
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) {
                return !$property->isStatic();
            }
        );
    }

    protected function getJsonObjects(\RegexIterator $phpFiles)
    {
        $jsonObjects = [];
        foreach ($phpFiles as $phpFile) {
            $class = $this->getClassName($phpFile->getRealPath());

            if (!empty($class)) {
                if (in_array('Commercetools\Core\Model\Common\JsonObject', class_parents($class))) {
                    $jsonObjects[] = $class;
                }
            }
        }

        return $jsonObjects;
    }

    protected function getCollectionObjects(\RegexIterator $phpFiles)
    {
        $collectionObjects = [];
        foreach ($phpFiles as $phpFile) {
            $class = $this->getClassName($phpFile->getRealPath());

            if (!empty($class)) {
                if (in_array('Commercetools\Core\Model\Common\Collection', class_parents($class))) {
                    $collectionObjects[] = $class;
                }
            }
        }

        return $collectionObjects;
    }

    protected function getRequestObjects(\RegexIterator $phpFiles)
    {
        $requestObjects = [];
        foreach ($phpFiles as $phpFile) {
            $class = $this->getClassName($phpFile->getRealPath());

            if (!empty($class)) {
                if (in_array('Commercetools\Core\Request\AbstractApiRequest', class_parents($class))) {
                    $requestObjects[] = $class;
                }
            }
        }

        return $requestObjects;
    }

    protected function getClassName($fileName)
    {
        $tokens = $this->tokenize($fileName);
        $namespace = '';
        for ($index = 0; isset($tokens[$index]); $index++) {
            if (!isset($tokens[$index][0])) {
                continue;
            }
            if (T_NAMESPACE === $tokens[$index][0]) {
                $index += 2; // Skip namespace keyword and whitespace
                while (isset($tokens[$index]) && is_array($tokens[$index])) {
                    $namespace .= $tokens[$index++][1];
                }
            }
            if (T_CLASS === $tokens[$index][0]) {
                $index += 2; // Skip class keyword and whitespace
                $class = $namespace.'\\'.$tokens[$index][1];
                return $class;
            }
        }

        return null;
    }

    protected function tokenize($fileName)
    {
        $content = file_get_contents($fileName);
        return token_get_all($content);
    }

    protected function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
