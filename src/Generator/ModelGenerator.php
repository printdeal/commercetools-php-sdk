<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\ClassMap;
use Commercetools\Model\Collection;
use Commercetools\Model\JsonObject;
use Commercetools\Model\ResourceClassMap;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpParser\Builder\Param;
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
    const COLLECTION_SUFFIX = 'Collection';

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
            DiscriminatorValue::class,
            Collectable::class,
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
        $this->ensureDirExists($this->outputPath);
        $outputPath = realpath($this->outputPath);

        $jsonResourceVisitor = $this->getJsonResources($path);

        $this->generateClassMap($jsonResourceVisitor->getResourceClasses(), $this->namespace, $outputPath);

        $this->generateModels($jsonResourceVisitor->getResourceClasses(), $this->namespace, $path, $outputPath);
        $this->generateDiscriminatorResolvers(
            $jsonResourceVisitor->getDiscriminatorClasses(),
            $jsonResourceVisitor->getDiscriminatorValueClasses(),
            $this->namespace,
            $path,
            $outputPath
        );
        $this->generateCollectionInterfaces($jsonResourceVisitor->getCollectionClasses(), $this->namespace, $path, $outputPath);
    }

    protected function generateClassMap($resourceClasses, $namespace, $outputPath)
    {
        $factory = new BuilderFactory();
        $builder = $factory->namespace($namespace);
        $builder->addStmt($factory->use(ClassMap::class));
        $classBuilder = $factory->class('ResourceModelClassMap')->extend('ClassMap');

        $types = [];
        foreach ($resourceClasses as $className => $resourceClass) {
            /**
             * @var ReflectionClass $reflectedClass
             */
            $reflectedClass = $resourceClass[ReflectionClass::class];
            $types[] = new Expr\ArrayItem(
                new Expr\ClassConstFetch(
                    new Node\Name('\\' . $reflectedClass->getName() . static::MODEL_SUFFIX), 'class'
                ),
                new Expr\ClassConstFetch(
                    new Node\Name('\\' . $reflectedClass->getName()), 'class'
                )
            );
        }
        $classBuilder->addStmt(
            $factory->property('types')->makeProtected()->makeStatic()->setDefault(
                new Expr\Array_($types, ['kind' => Expr\Array_::KIND_SHORT])
            )
        );
        $builder->addStmt($classBuilder);

        $fileName = $outputPath . '/ResourceModelClassMap.php';
        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(new NamespaceChangeVisitor($namespace, $namespace)); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $this->writeClass($fileName, $stmts);
    }

    public function generateModels($resourceClasses, $namespace, $path, $outputPath)
    {
        foreach ($resourceClasses as $className => $resourceClass) {
            $this->generateReadModelFromInterface($resourceClass[ReflectionClass::class], $namespace, $path, $outputPath);
        }
    }

    protected function getJsonResources($path)
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
        return $jsonResourceVisitor;
    }

    protected function generateReadModelFromInterface(\ReflectionClass $reflectedClass, $namespace, $path, $outputPath)
    {
        $reader = new AnnotationReader();
        $factory = new BuilderFactory();

        $builder = $factory->namespace($reflectedClass->getNamespaceName());

        $classUses[$reflectedClass->getName()] = $factory->use($reflectedClass->getName());
        $classUses[] = $factory->use(ResourceClassMap::class);

        $modelPath = str_replace($path, $outputPath, dirname($reflectedClass->getFileName()));

        $className = $reflectedClass->getShortName() . self::MODEL_SUFFIX;
        $classBuilder = $factory->class($className)
            ->implement($reflectedClass->getShortName());

        list($parentClassShortName, $parentClass) = $this->getParentClass($reflectedClass);
        $classUses[$parentClassShortName] = $factory->use($parentClass);
        $classBuilder = $classBuilder->extend($parentClassShortName);

        $propertyStmts = $this->createPropertiesFromResourceInterface($reflectedClass);
        list($getterUses, $propertyGetterStmts) = $this->createPropertyGetterFromResourceInterface($reflectedClass);
        $classUses = array_merge($classUses, $getterUses);
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

    protected function generateCollectionInterfaces($collectionClasses, $namespace, $path, $outputPath)
    {
        foreach ($collectionClasses as $className => $collectionClass) {
            $this->generateCollectionInterface($collectionClass, $namespace, $path, $outputPath);
        }
    }

    protected function generateCollectionInterface($collectionClass, $namespace, $path, $outputPath)
    {
        $factory = new BuilderFactory();

        /**
         * @var ReflectionClass $reflectedClass
         */
        $reflectedClass = $collectionClass[\ReflectionClass::class];
        $builder = $factory->namespace($reflectedClass->getNamespaceName());

        $modelPath = str_replace($path, $outputPath, dirname($reflectedClass->getFileName()));

        $classUses['Collection'] = $factory->use(Collection::class);
        $className = $reflectedClass->getShortName() . self::COLLECTION_SUFFIX;
        $classBuilder = $factory->interface($className)
            ->extend('Collection');
//            ->extend($reflectedClass->getShortName());

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

    protected function getParentClass(ReflectionClass $reflectedClass)
    {
        $reader = new AnnotationReader();

        $reflectedJsonObject = new ReflectionClass(JsonObject::class);
        $parentClassShortName = $reflectedJsonObject->getShortName();
        $parentClassName = $reflectedJsonObject->getName();

        $annotation = $reader->getClassAnnotation($reflectedClass, JsonResource::class);
        $interfaceParents = $reflectedClass->getInterfaceNames();
        if ($annotation instanceof JsonResource && $annotation->type) {
            $uses = $this->getUses($reflectedClass);
            $parentClassShortName = $parentClassName = $annotation->type;
            if (isset($uses[$parentClassShortName])) {
                $parentClassName = $uses[$parentClassShortName]['name'];
            }
        } elseif ($reflectedClass->isInterface() && count($interfaceParents) > 0) {
            $parentClass = new ReflectionClass(current($interfaceParents));
            $annotation = $reader->getClassAnnotation($parentClass, JsonResource::class);
            if ($annotation instanceof JsonResource) {
                $parentNamespace = $parentClass->getNamespaceName();
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
        $classUses = [];
        foreach ($reflectedClass->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass()->getName() !== $reflectedClass->getName()) {
                continue;
            }
            $propertyName = lcfirst(str_replace('get', '', $reflectionMethod->getName()));

            $annotation = $reader->getMethodAnnotation($reflectionMethod, JsonField::class);
            list($getterUses, $propertyMethod) = $this->getPropertyGetter($reflectionMethod, $propertyName, $annotation);
            $classUses = array_merge($classUses, $getterUses);

            $stmts[] = $propertyMethod;
        }

        return [$classUses, $stmts];
    }

    public function generateDiscriminatorResolvers(
        $discriminatorClasses,
        $discriminatorValues,
        $namespace,
        $path,
        $outputPath
    ) {
        foreach ($discriminatorClasses as $discriminatorClass => $infos) {
            /**
             * @var ReflectionClass $reflectedClass
             */
            $reflectedClass = $infos[ReflectionClass::class];
            $factory = new BuilderFactory();
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
                /**
                 * @var ReflectionClass $reflectedValueClass
                 */
                $reflectedValueClass = $discriminatorValue[ReflectionClass::class];
                $types[] = new Expr\ArrayItem(
                    new Expr\ClassConstFetch(
                        new Node\Name($reflectedValueClass->getShortName()), 'class'
                    ),
                    new Scalar\String_($discriminatorValue[DiscriminatorValue::class]->value)
                );
                $builder->addStmt($factory->use($reflectedValueClass->getName()));
            }
            $classBuilder->addStmt(new Stmt\ClassConst([
                new Node\Const_('TYPES', new Expr\Array_($types, ['kind' => Expr\Array_::KIND_SHORT]))
            ]));
            $classBuilder->addStmt($this->getDiscriminatorResolverMethod($reflectedClass));
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

    public function getDiscriminatorResolverMethod(\ReflectionClass $reflectionClass)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('discriminatorType')
            ->makePublic()
            ->makeStatic()
            ->addParam(
                $factory->param('data')->setTypeHint('array')
            )
            ->addParam(
                $factory->param('discriminatorName')
            )
            ->getNode();
        $body = '$types = static::TYPES;
    $discriminator = isset($data[$discriminatorName]) ? $data[$discriminatorName] : \'\';
    return isset($types[$discriminator]) ? $types[$discriminator] : ' . $reflectionClass->getShortName() . static::MODEL_SUFFIX . '::class;';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
    }

    public function getPropertyGetter(\ReflectionMethod $propertyMethod, $propertyName, $annotation)
    {
        $classUses = [];
        $factory = new BuilderFactory();
        $method = $factory->method($propertyMethod->getName())
            ->makePublic()
            ->getNode();
        $uses = $this->getUses($propertyMethod->getDeclaringClass());
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
            $typeAnnotation = $this->getTypeAnnotation($propertyMethod->getDeclaringClass()->getNamespaceName(), $annotation->type, $uses);
            if ($typeAnnotation instanceof Discriminator) {
                if (isset($uses[$annotation->type])) {
                    $useName = $uses[$annotation->type];
                    $classUses[$annotation->type . 'DiscriminatorResolver'] = $factory->use($useName['name'] . 'DiscriminatorResolver');
                } else {
                    $classUses[$annotation->type . 'DiscriminatorResolver'] = $factory->use($propertyMethod->getDeclaringClass()->getNamespaceName() . '\\' . $annotation->type . 'DiscriminatorResolver');
                }
                $body .= '    $type = ' . $annotation->type . 'DiscriminatorResolver::discriminatorType($value, \'' . $typeAnnotation->name . '\');';
                $body .= '    $mappedClass = ResourceClassMap::getMappedClass($type);';
            } else {
                $body .= '    $mappedClass = ResourceClassMap::getMappedClass('.$annotation->type.'::class);';
                if (isset($uses[$annotation->type])) {
                    $useName = $uses[$annotation->type];
                    $classUses[$annotation->type] = $factory->use($useName['name']);
                } else {
                    $classUses[$annotation->type] = $factory->use($propertyMethod->getDeclaringClass()->getNamespaceName() . '\\' . $annotation->type);
                }
            }
            $body .= '    $this->' . $propertyName . ' = new $mappedClass(' . $params . ');' . PHP_EOL;
        }
        $body .= '}' . PHP_EOL . 'return $this->' . $propertyName . ';' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return [$classUses, $method];
    }

    protected function getTypeAnnotation($namespace, $type, $uses)
    {
        $reader = new AnnotationReader();
        $typeAnnotation = false;
        if (isset($uses[$type])) {
            $typeClass = new ReflectionClass($uses[$type]['name']);
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
