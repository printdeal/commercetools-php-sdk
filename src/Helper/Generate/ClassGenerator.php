<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

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
            DiscriminatorColumn::class,
            CollectionType::class,
            Draftable::class,
            DraftableCollection::class,
            ReferenceType::class
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

        $this->generateDataFiles($phpFiles);
    }

    public function generateDataFiles($phpFiles)
    {
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
        $this->generateFiles($files, $this->newNamespace);
    }

    protected function generateFiles($files, $namespace)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);

        $draftVisitor = new DraftVisitor();
        $propertyVisitor = new JsonFieldGetterVisitor();
        foreach ($files as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor($draftVisitor);
            $traverser->addVisitor($propertyVisitor);
            $traverser->addVisitor(new ReferenceVisitor());
            $traverser->addVisitor(new CollectionVisitor());
            $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace));
            $traverser->traverse($stmts);


            $this->writeClass($file, $stmts);
        }

        $files = $this->generateBaseDrafts($draftVisitor->getDraftableClasses(), $namespace);
        $this->enrichDrafts($files, $namespace);
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

    protected function enrichDrafts($files, $namespace)
    {
        foreach ($files as $file) {
            $code = file_get_contents($file);
            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
            $stmts = $parser->parse($code);

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor(new JsonFieldGetterVisitor());
            $traverser->addVisitor(new DraftSetterVisitor());
            $traverser->addVisitor(new CollectionVisitor());
            $traverser->addVisitor(new ReferenceVisitor());
            $traverser->addVisitor(new CollectionSetterVisitor());
            $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace));
            $traverser->traverse($stmts);
            $this->writeClass($file, $stmts);
        }
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
                $classBuilder = $factory->class($reflectedClass->getShortName() . 'Draft')
                    ->setDocComment($reflectedClass->getDocComment())
                    ->extend($reflectedClass->getParentClass()->getShortName());

                list($types, $uses) = $this->getUses($reflectedClass);
                $builder = $factory->namespace($reflectedClass->getNamespaceName());

                $classUses = [
                    'JsonField' => $factory->use(JsonField::class),
                    'Draftable' => $factory->use(Draftable::class),
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
                    if ($property->getDeclaringClass() != $reflectedClass) {
                        continue;
                    }
                    $draftProperty = $factory
                        ->property($property->getName())
                        ->setDocComment($property->getDocComment());
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
                            $classUses[$type] = $node;
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
                $classBuilder = $factory->class($reflectedClass->getShortName() . 'Draft')
                    ->setDocComment($reflectedClass->getDocComment())
                    ->extend($reflectedClass->getParentClass()->getShortName());

                $builder = $factory->namespace($reflectedClass->getNamespaceName());

                $classUses = [
                    'JsonField' => $factory->use(JsonField::class),
                    'DraftableCollection' => $factory->use(DraftableCollection::class),
                ];
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
