<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\JsonObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use ReflectionClass;

class ModelGenerator
{
    const MODEL_SUFFIX = 'Model';

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
    protected $namespace;

    public function __construct($path, $outputPath, $namespace)
    {
        $this->path = $path;
        $this->outputPath = $outputPath;
        $this->namespace = $namespace;

        $annotationClasses = [
            JsonResource::class,
            JsonField::class,
            Discriminator::class,
            DiscriminatorValue::class
//            JsonFieldSetter::class,
//            CollectionType::class,
//            Draftable::class,
//            DraftableCollection::class,
//            ReferenceType::class,
//            CollectionSetter::class
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

        list($interfaceClasses, $discriminatorClasses, $discriminatorValues) = $this->getInterfaceClasses($path);

        $this->generateModels($interfaceClasses, $this->namespace, $path, $outputPath);
        $this->generateDiscriminatorResolvers(
            $discriminatorClasses,
            $discriminatorValues,
            $this->namespace,
            $path,
            $outputPath
        );
//        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($outputPath));
//        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
//        $this->generateFiles($phpFiles, $this->namespace);
    }

    protected function getInterfaceClasses($path)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        $interfaceVisitor = new ModelInterfaceVisitor();
        $discriminatorVisitor = new DiscriminatorVisitor();
        $discriminatorValueVisitor = new DiscriminatorValueVisitor();
        foreach ($phpFiles as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor($interfaceVisitor);
            $traverser->addVisitor($discriminatorVisitor);
            $traverser->addVisitor($discriminatorValueVisitor);

            $traverser->traverse($stmts);
        }
        return [
            $interfaceVisitor->getInterfaceClasses(),
            $discriminatorVisitor->getDiscriminatorClasses(),
            $discriminatorValueVisitor->getDiscriminatorValues()
        ];
    }

    protected function generateReadModelFromInterface(\ReflectionClass $reflectedClass, $namespace, $path, $outputPath)
    {
        $reader = new AnnotationReader();
        $factory = new BuilderFactory();

        $diffNamespace = $this->getDiffNamespace($path, $reflectedClass);

        $modelNamespace = $namespace . $diffNamespace;

        $builder = $factory->namespace($modelNamespace);

        $classUses[$reflectedClass->getName()] = $factory->use($reflectedClass->getName());

        $modelPath = str_replace($path, $outputPath, dirname($reflectedClass->getFileName()));

        $className = $reflectedClass->getShortName() . self::MODEL_SUFFIX;
        $classBuilder = $factory->class($className)
            ->implement($reflectedClass->getShortName());

        list($types, $uses) = $this->getUses($reflectedClass);

        $interfaceParents = $reflectedClass->getInterfaceNames();
        $annotation = $reader->getClassAnnotation($reflectedClass, JsonResource::class);
        if ($annotation instanceof JsonResource && $annotation->type) {
            $parentClassName = $annotation->type;
        } elseif (count($interfaceParents)) {
            $parentClass = new ReflectionClass(current($interfaceParents));
            $diffNamespace = $this->getDiffNamespace($path, $parentClass);
            $parentClassName = $parentClass->getShortName() . self::MODEL_SUFFIX;
            $parentNamespace = $namespace . ($diffNamespace ? '\\' . $diffNamespace : '');
            if ($parentNamespace !== $modelNamespace) {
                $parentClassUse = $parentNamespace . '\\' . $parentClass->getShortName() . self::MODEL_SUFFIX;
                $classUses[$parentClassName] = $factory->use($parentClassUse);
            }
        } else {
            $parentClassName = 'JsonObject';
            $classUses[$parentClassName] = $factory->use(JsonObject::class);
        }

        if (isset($uses[$parentClassName])) {
            $use = $uses[$parentClassName];
            $node = $factory->use($use['name']);
            if (isset($use['alias'])) {
                $node->as($use['alias']);
            }
            $classUses[$parentClassName] = $node->getNode();
        }

        $classBuilder = $classBuilder->extend($parentClassName);


        foreach ($reflectedClass->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass()->getName() !== $reflectedClass->getName()) {
                continue;
            }
            $propertyName = lcfirst(str_replace('get', '', $reflectionMethod->getName()));

            $property = $factory
//                ->setDocComment($docComment);
                ->property($propertyName)
            ;

            $annotation = $reader->getMethodAnnotation($reflectionMethod, JsonField::class);
            $propertyMethod = $this->getPropertyGetter($reflectionMethod, $propertyName, $annotation);

            if (isset($types[$reflectionMethod->getName()])) {
                $type = $types[$reflectionMethod->getName()];
                if (isset($uses[$type])) {
                    $use = $uses[$type];
                    $node = $factory->use($use['name']);
                    if (isset($use['alias'])) {
                        $node->as($use['alias']);
                    }
                    $classUses[$type] = $node->getNode();
                }
            }

            $classBuilder->addStmt($property);
            $classBuilder->addStmt($propertyMethod);
        }

        $builder->addStmts(array_values($classUses));
        $builder->addStmt($classBuilder);
        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace)); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        $this->writeClass($fileName, $stmts);
    }

    protected function getDiffNamespace($path, ReflectionClass $reflectionClass)
    {
        return str_replace('/', '\\', str_replace($path, '', dirname($reflectionClass->getFileName())));
    }

    public function generateDiscriminatorResolvers(
        $discriminatorClasses,
        $discriminatorValues,
        $namespace,
        $path,
        $outputPath
    ) {
        foreach ($discriminatorClasses as $discriminatorClass => $annotation) {
            $factory = new BuilderFactory();
            $reflectedClass = new \ReflectionClass($discriminatorClass);
            $builder = $factory->namespace($namespace);

            $modelPath = str_replace($path, $outputPath, dirname($reflectedClass->getFileName()));

            $className = $reflectedClass->getShortName() . 'DiscriminatorResolver';
            $classBuilder = $factory->class($className);
            $types = [];
            /**
             * @var DiscriminatorValue $discriminatorValue
             */
            $classValues = $discriminatorValues[$discriminatorClass];
            foreach ($classValues as $valueClass => $discriminatorValue) {
                $reflectedValueClass = new ReflectionClass($valueClass);
                $diffNamespace = $this->getDiffNamespace($path, $reflectedValueClass);
                $valueModelClassNamespace = $namespace . ($diffNamespace ? '\\' . $diffNamespace : '');
                $valueModelClass = $valueModelClassNamespace . '\\' . $reflectedValueClass->getShortName() . self::MODEL_SUFFIX;
                $types[] = new Expr\ArrayItem(
                    new Expr\ClassConstFetch(
                        new Node\Name($valueModelClass), 'class'
                    ),
                    new Scalar\String_($discriminatorValue->value)
                );
            }
            $classBuilder->addStmt(new Stmt\ClassConst([
                new Node\Const_('TYPES', new Expr\Array_($types, ['kind' => Expr\Array_::KIND_SHORT]))
            ]));
            $builder->addStmt($classBuilder);

            $node = $builder->getNode();
            $stmts = [$node];

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace)); // we will shorten the resolved names
            $traverser->traverse($stmts);

            $fileName = $modelPath . '/' . $className . '.php';
            $this->writeClass($fileName, $stmts);
        }
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

    public function generateModels($interfaceClasses, $namespace, $path, $outputPath)
    {
        /**
         * @var \ReflectionClass $reflectedClass
         */
        foreach ($interfaceClasses as $reflectedClass) {
            $this->generateReadModelFromInterface($reflectedClass, $namespace, $path, $outputPath);
        }
    }

    protected function writeClass($filename, $stmts)
    {
        $printer = new PrettyPrinter\Standard();
        $this->ensureDirExists(dirname($filename));
        file_put_contents($filename, '<?php ' . PHP_EOL . $printer->prettyPrint($stmts));
    }


    public function getPropertyGetter(\ReflectionMethod $propertyMethod, $propertyName, $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method($propertyMethod->getName())
            ->makePublic()
            ->getNode();
        $body = 'if (is_null($this->'. $propertyName . ') &&
    !is_null($value = $this->raw(\'' . $propertyName . '\'))) {';
        if (is_null($annotation->type)) {
            $body .= '    $this->' .$propertyName .
                ' = $value;' . PHP_EOL;
        } elseif (in_array($annotation->type, ['int', 'bool', 'string', 'float', 'array'])) {
            $body .= '    $this->' . $propertyName .
                ' = (' . $annotation->type . ')$value;' . PHP_EOL;
        } else {
            $params = '$value';
            if ($annotation->params) {
                foreach ($annotation->params as $param) {
                    $methodName =  'get'.ucfirst($param);
                    $params .= ', $this->' . $methodName . '()';
                }
            }
//            $typeAnnotation = $this->getTypeAnnotation($annotation->type);
//            if ($typeAnnotation instanceof DiscriminatorColumn) {
//                $body .= '    $type = ' . $typeAnnotation->callback . '($value, \'' . $typeAnnotation->name . '\');';
//                $body .= '    $this->' . $propertyName . ' = new $type(' . $params . ');' . PHP_EOL;
//            } else {
                $body .= '    $this->' . $propertyName .
                    ' = new '.$annotation->type.'(' . $params . ');' . PHP_EOL;
//            }
        }
        $body .= '}' . PHP_EOL . 'return $this->' . $propertyName . ';' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
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
