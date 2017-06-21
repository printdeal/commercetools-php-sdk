<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\JsonObject;
use Commercetools\Model\ResourceClassMap;
use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

use ReflectionClass;

class ResourceProcessor extends AbstractProcessor
{
    private $path;
    private $outputPath;

    /**
     * ResourceProcessor constructor.
     * @param $namespace
     * @param $path
     * @param $outputPath
     */
    public function __construct($path, $outputPath)
    {
        $this->path = $path;
        $this->outputPath = $outputPath;
    }

    public function getAnnotation()
    {
        return JsonResource::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ReflectionClass $class, $annotation)
    {
        $file = $this->generateReadModelFromInterface($class);

        return [$file];
    }

    protected function generateReadModelFromInterface(\ReflectionClass $reflectedClass)
    {
        $factory = new BuilderFactory();

        $builder = $factory->namespace($reflectedClass->getNamespaceName());

        $classUses[$reflectedClass->getName()] = $factory->use($reflectedClass->getName());
        $classUses[] = $factory->use(ResourceClassMap::class);

        $modelPath = str_replace($this->path, $this->outputPath, dirname($reflectedClass->getFileName()));

        $className = $reflectedClass->getShortName() . static::MODEL_SUFFIX;
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
        $traverser->addVisitor(
            new NamespaceChangeVisitor($reflectedClass->getNamespaceName(), $reflectedClass->getNamespaceName())
        ); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        return $this->writeClass($fileName, $stmts);
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
            list($getterUses, $propertyMethod) = $this->getPropertyGetter(
                $reflectionMethod,
                $propertyName,
                $annotation
            );
            $classUses = array_merge($classUses, $getterUses);

            $stmts[] = $propertyMethod;
        }

        return [$classUses, $stmts];
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

    public function getPropertyGetter(\ReflectionMethod $propertyMethod, $propertyName, $annotation)
    {
        $classUses = [];
        $factory = new BuilderFactory();
        $method = $factory->method($propertyMethod->getName())
            ->makePublic()
            ->getNode();
        $uses = $this->getUses($propertyMethod->getDeclaringClass());
        $body = 'if (is_null($this->'. $propertyName . ')) {
    $value = $this->raw(\'' . $propertyName . '\');';
        if (is_null($annotation->type)) {
            $body .= '    if (!is_null($value)) {
        $this->' .$propertyName . ' = $value;
    }' . PHP_EOL;
        } elseif (in_array($annotation->type, ['int', 'bool', 'string', 'float', 'array'])) {
            $body .= '        if (!is_null($value)) {
        $this->' . $propertyName . ' = (' . $annotation->type . ')$value;
    }' . PHP_EOL;
        } else {
            $params = '$value';
            if ($annotation->params) {
                foreach ($annotation->params as $param) {
                    $methodName =  'get'.ucfirst($param);
                    $params .= ', $this->' . $methodName . '()';
                }
            }
            $typeAnnotation = $this->getTypeAnnotation($propertyMethod, $annotation->type, $uses);
            $namespace = $propertyMethod->getDeclaringClass()->getNamespaceName();
            if ($typeAnnotation instanceof Discriminator) {
                if (isset($uses[$annotation->type])) {
                    $useName = $uses[$annotation->type];
                    $classUses[$annotation->type . 'DiscriminatorResolver'] = $factory->use(
                        $useName['name'] . 'DiscriminatorResolver'
                    );
                } else {
                    $classUses[$annotation->type . 'DiscriminatorResolver'] = $factory->use(
                        $namespace . '\\' . $annotation->type . 'DiscriminatorResolver'
                    );
                }
                $body .= '    $type = ' . $annotation->type . 'DiscriminatorResolver::discriminatorType' .
                    '($value, \'' . $typeAnnotation->name . '\');';
                $body .= '    $mappedClass = ResourceClassMap::getMappedClass($type);';
            } else {
                $body .= '    $mappedClass = ResourceClassMap::getMappedClass('.$annotation->type.'::class);';
                if (isset($uses[$annotation->type])) {
                    $useName = $uses[$annotation->type];
                    $classUses[$annotation->type] = $factory->use($useName['name']);
                } else {
                    $classUses[$annotation->type] = $factory->use($namespace . '\\' . $annotation->type);
                }
            }
            $body .= '    if (is_null($value)) { return new $mappedClass([]); }' . PHP_EOL;
            $body .= '    $this->' . $propertyName . ' = new $mappedClass(' . $params . ');' . PHP_EOL;
        }
        $body .= '}' . PHP_EOL . 'return $this->' . $propertyName . ';' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return [$classUses, $method];
    }

    protected function getTypeAnnotation(\ReflectionMethod $method, $type, $uses)
    {
        $namespace = $method->getDeclaringClass()->getNamespaceName();
        $reader = new AnnotationReader();
        $typeAnnotation = false;
        $className = $namespace . '\\' . $type;
        if (isset($uses[$type])) {
            $typeClass = new ReflectionClass($uses[$type]['name']);
            $typeAnnotation = $reader->getClassAnnotation($typeClass, Discriminator::class);
        } elseif (class_exists($className) || interface_exists($className)) {
            $typeClass = new ReflectionClass($className);
            $typeAnnotation = $reader->getClassAnnotation($typeClass, Discriminator::class);
        }
        return $typeAnnotation;
    }
}
