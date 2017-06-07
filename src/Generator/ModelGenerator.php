<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\ClassMap;
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

        $resourceClasses = $this->getResourceClasses($path);

        $this->generateClassMap($resourceClasses, $this->namespace, $path, $outputPath);

        $this->generateModels($resourceClasses, $this->namespace, $path, $outputPath);
//        $this->generateDiscriminatorResolvers(
//            $discriminatorClasses,
//            $discriminatorValues,
//            $this->namespace,
//            $path,
//            $outputPath
//        );
//        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($outputPath));
//        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
//        $this->generateFiles($phpFiles, $this->namespace);
    }

    protected function generateClassMap($resourceClasses, $namespace, $path, $outputPath)
    {
        $factory = new BuilderFactory();
        $builder = $factory->namespace($namespace);
        $builder->addStmt($factory->use(ClassMap::class));
        $classBuilder = $factory->class('ResourceClassMap')->extend('ClassMap');

        $types = [];
        foreach ($resourceClasses as $className => $resourceClass) {
            /**
             * @var ReflectionClass $reflectedClass
             */
            $reflectedClass = $resourceClass[ReflectionClass::class];
            $relativeNamespace = $this->relativeNamespace($path, $reflectedClass);
            $modelClass = $namespace . '\\' . $relativeNamespace . ($relativeNamespace ?  '\\' : '')  .
                $reflectedClass->getShortName() . static::MODEL_SUFFIX;
//                $builder->addStmt($factory->use($reflectedClass->getName()));
                $types[] = new Expr\ArrayItem(
                    new Expr\ClassConstFetch(
                        new Node\Name('\\' . $modelClass), 'class'
                    ),
                    new Expr\ClassConstFetch(
                        new Node\Name('\\' . $reflectedClass->getName()), 'class'
                    )
                );

        }
        $classBuilder->addStmt(
            $factory->property('types')->makeProtected()->setDefault(
                new Expr\Array_($types, ['kind' => Expr\Array_::KIND_SHORT])
            )
        );
        $builder->addStmt($classBuilder);

        $fileName = $outputPath . '/ResourceClassMap.php';
        $node = $builder->getNode();
        $stmts = [$node];
        $this->writeClass($fileName, $stmts);
    }

    public function generateModels($resourceClasses, $namespace, $path, $outputPath)
    {
        foreach ($resourceClasses as $className => $resourceClass) {
            $this->generateReadModelFromInterface($resourceClass[ReflectionClass::class], $namespace, $path, $outputPath);
        }
    }

    protected function getResourceClasses($path)
    {
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $phpFiles = new \RegexIterator($allFiles, '/\.php$/');
        $jsonResourceVisitor = new JsonResourceVisitor();
        foreach ($phpFiles as $file) {
            $code = file_get_contents($file);
            $stmts = $parser->parse($code);
            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver()); // we will need resolved names
            $traverser->addVisitor($jsonResourceVisitor);

            $traverser->traverse($stmts);
        }
        return $jsonResourceVisitor->getResourceClasses();
    }

    protected function generateReadModelFromInterface(\ReflectionClass $reflectedClass, $namespace, $path, $outputPath)
    {
        $reader = new AnnotationReader();
        $factory = new BuilderFactory();

        $diffNamespace = $this->relativeNamespace($path, $reflectedClass);
        $modelNamespace = $namespace . ($diffNamespace ? '\\' : ''). $diffNamespace;

        $builder = $factory->namespace($modelNamespace);

        $classUses[$reflectedClass->getName()] = $factory->use($reflectedClass->getName());

        $modelPath = str_replace($path, $outputPath, dirname($reflectedClass->getFileName()));

        $className = $reflectedClass->getShortName() . self::MODEL_SUFFIX;
        $classBuilder = $factory->class($className)
            ->implement($reflectedClass->getShortName());

        list($parentClassShortName, $parentClass) = $this->getParentClass($reflectedClass, $namespace, $path);
        $classUses[$parentClassShortName] = $factory->use($parentClass);
        $classBuilder = $classBuilder->extend($parentClassShortName);

        $propertyStmts = $this->createPropertiesFromResourceInterface($reflectedClass);
        $propertyGetterStmts = $this->createPropertyGetterFromResourceInterface($reflectedClass);
        $classBuilder->addStmts($propertyStmts);
        $classBuilder->addStmts($propertyGetterStmts);

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

    protected function getParentClass(ReflectionClass $reflectedClass, $namespace, $path)
    {
        $reader = new AnnotationReader();

        $reflectedJsonObject = new ReflectionClass(JsonObject::class);
        $parentClassShortName = $reflectedJsonObject->getShortName();
        $parentClassName = $reflectedJsonObject->getName();

        $annotation = $reader->getClassAnnotation($reflectedClass, JsonResource::class);
        $interfaceParents = $reflectedClass->getInterfaceNames();
        if ($annotation instanceof JsonResource && $annotation->type) {
            $uses = $this->getUses($reflectedClass);
            var_dump($uses);
            $parentClassShortName = $parentClassName = $annotation->type;
            if (isset($uses[$parentClassShortName])) {
                $parentClassName = $uses[$parentClassShortName]['name'];
            }
        } elseif ($reflectedClass->isInterface() && count($interfaceParents) > 0) {
            $parentClass = new ReflectionClass(current($interfaceParents));
            $annotation = $reader->getClassAnnotation($parentClass, JsonResource::class);
            if ($annotation instanceof JsonResource) {
                $parentNamespace = $namespace . $this->relativeNamespace($path, $parentClass);
                $parentClassShortName = $parentClass->getShortName() . self::MODEL_SUFFIX;
                $parentClassName = $parentNamespace . '\\' . $parentClassShortName;
            }
        }

        return [$parentClassShortName, $parentClassName];
    }
    protected function createPropertiesFromResourceInterface(ReflectionClass $reflectedClass)
    {
        $factory = new BuilderFactory();
        $stmts = [];
        foreach ($reflectedClass->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass()->getName() !== $reflectedClass->getName()) {
                continue;
            }
            $propertyName = lcfirst(str_replace('get', '', $reflectionMethod->getName()));

            $property = $factory
                ->property($propertyName)
            ;

            $stmts[] = $property;
        }

        return $stmts;
    }

    protected function createPropertyGetterFromResourceInterface(ReflectionClass $reflectedClass)
    {
        $reader = new AnnotationReader();
        $stmts = [];
        foreach ($reflectedClass->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass()->getName() !== $reflectedClass->getName()) {
                continue;
            }
            $propertyName = lcfirst(str_replace('get', '', $reflectionMethod->getName()));

            $annotation = $reader->getMethodAnnotation($reflectionMethod, JsonField::class);
            $propertyMethod = $this->getPropertyGetter($reflectionMethod, $propertyName, $annotation);

//            if (isset($types[$reflectionMethod->getName()])) {
//                $type = $types[$reflectionMethod->getName()];
//                if (isset($uses[$type])) {
//                    $use = $uses[$type];
//                    $node = $factory->use($use['name']);
//                    if (isset($use['alias'])) {
//                        $node->as($use['alias']);
//                    }
//                    $classUses[$type] = $node->getNode();
//                }
//            }

            $stmts[] = $propertyMethod;
        }

        return $stmts;
    }

    protected function relativeNamespace($path, ReflectionClass $reflectionClass)
    {
        return trim(str_replace('/', '\\', str_replace($path, '', dirname($reflectionClass->getFileName()))), '\\');
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
                $diffNamespace = $this->relativeNamespace($path, $reflectedValueClass);
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
        return $useVisitor->getUses();
    }

    protected function writeClass($filename, $stmts)
    {
        $printer = new MyPrettyPrinter();
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
            $typeAnnotation = $this->getTypeAnnotation($propertyMethod->getDeclaringClass()->getNamespaceName(), $annotation->type);
            if ($typeAnnotation instanceof Discriminator) {
                $body .= '    $type = ' . $typeAnnotation->callback . '($value, \'' . $typeAnnotation->name . '\');';
                $body .= '    $this->' . $propertyName . ' = new $type(' . $params . ');' . PHP_EOL;
            } else {
                $body .= '    $this->' . $propertyName .
                    ' = new '.$annotation->type.'(' . $params . ');' . PHP_EOL;
            }
        }
        $body .= '}' . PHP_EOL . 'return $this->' . $propertyName . ';' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
    }

    protected function getTypeAnnotation($namespace, $type)
    {
        $reader = new AnnotationReader();
        $typeAnnotation = false;
        var_dump($namespace);
        if (isset($this->uses[$type])) {
            $typeClass = new ReflectionClass($this->uses[$type]);
            $typeAnnotation = $reader->getClassAnnotation($typeClass, Discriminator::class);
        } elseif (class_exists($namespace . '\\' . $type)) {
            $typeClass = new ReflectionClass($namespace . '\\' . $type);
            $typeAnnotation = $reader->getClassAnnotation($typeClass, Discriminator::class);
        }
        return $typeAnnotation;
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
