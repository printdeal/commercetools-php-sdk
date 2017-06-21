<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\JsonCollection;
use Commercetools\Model\ResourceClassMap;
use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use ReflectionClass;

class CollectionTypeProcessor extends AbstractProcessor
{
    private $path;
    private $outputPath;

    /**
     * @param $namespace
     * @param $path
     * @param $outputPath
     */
    public function __construct($path, $outputPath)
    {
        $this->path = $path;
        $this->outputPath = $outputPath;
    }

    public function process(ReflectionClass $class, $annotation)
    {
        if (!$annotation instanceof CollectionType) {
            return [];
        }
        $factory = new BuilderFactory();

        $builder = $factory->namespace($class->getNamespaceName());

        $classUses[$class->getName()] = $factory->use($class->getName());
        $classUses[] = $factory->use(ResourceClassMap::class);

        $modelPath = str_replace($this->path, $this->outputPath, dirname($class->getFileName()));

        $className = $class->getShortName() . static::MODEL_SUFFIX;
        $classBuilder = $factory->class($className)
            ->implement($class->getShortName());

        list($parentClassShortName, $parentClass) = $this->getParentClass($class);
        $classUses[$parentClassShortName] = $factory->use($parentClass);
        $classBuilder = $classBuilder->extend($parentClassShortName);

        $classBuilder->addStmt($this->getCollectionMap($class, $annotation));
        if (count($annotation->indexes) > 0) {
            $classBuilder->addStmt($this->getCollectionIndexer($annotation));
        }

        foreach ($annotation->indexes as $index) {
            $classBuilder->addStmt($this->getCollectionIndexGetter($annotation, $index));
        }

        $builder->addStmts(array_values($classUses));
        $builder->addStmt($classBuilder);
        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(
            new NamespaceChangeVisitor($class->getNamespaceName(), $class->getNamespaceName())
        ); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        return [$this->writeClass($fileName, $stmts)];
    }

    private function getCollectionIndexGetter(CollectionType $annotation, $index)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('by' . ucfirst($index))
            ->addParam($factory->param($index))
            ->makePublic()
            ->getNode();
        $body = 'return $this->valueByKey(\'' . $index . '\', $' . $index . ');';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
    }

    private function getCollectionIndexer(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('index')
            ->makeProtected()
            ->addParam($factory->param('data'))
            ->getNode();
        $body = '    foreach ($data as $key => $value) {' . PHP_EOL;
        foreach ($annotation->indexes as $index) {
            $body.= '        if (isset($value[\'' . $index . '\'])) {' . PHP_EOL;
            $body.= '            $this->addToIndex(\'' . $index . '\', $value[\'' . $index . '\'], $key);' . PHP_EOL;
            $body.= '        }' . PHP_EOL;
        }
        $body.= '    }' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
    }

    private function getCollectionMap(ReflectionClass $class, CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('map')
            ->addParam($factory->param('data'))
            ->addParam($factory->param('index'))
            ->makeProtected()
            ->getNode();
        $uses = $this->getUses($class);

        $body = '';
        if (is_null($annotation->elementType)) {
            $body .= 'return $data;' . PHP_EOL;
        } elseif (in_array($annotation->elementType, ['int', 'bool', 'string', 'float', 'array'])) {
            $body .= 'return (' . $annotation->elementType . ')$data;' . PHP_EOL;
        } else {
            $params = '$data';
            $typeAnnotation = $this->getTypeAnnotation($class, $annotation->elementType, $uses);
            if ($typeAnnotation instanceof Discriminator) {
                if (isset($uses[$annotation->elementType])) {
                    $useName = $uses[$annotation->elementType];
                    $classUses[$annotation->elementType . 'DiscriminatorResolver'] = $factory->use(
                        $useName['name'] . 'DiscriminatorResolver'
                    );
                } else {
                    $classUses[$annotation->elementType . 'DiscriminatorResolver'] = $factory->use(
                        $class->getNamespaceName() . '\\' . $annotation->elementType . 'DiscriminatorResolver'
                    );
                }
                $body .= '    $type = ' . $annotation->elementType . 'DiscriminatorResolver::discriminatorType' .
                    '($value, \'' . $typeAnnotation->name . '\');';
                $body .= '    $mappedClass = ResourceClassMap::getMappedClass($type);';
            } else {
                $body .= '    $mappedClass = ResourceClassMap::getMappedClass('.$annotation->elementType.'::class);';
                if (isset($uses[$annotation->elementType])) {
                    $useName = $uses[$annotation->elementType];
                    $classUses[$annotation->elementType] = $factory->use($useName['name']);
                } else {
                    $classUses[$annotation->elementType] = $factory->use(
                        $class->getNamespaceName() . '\\' . $annotation->elementType
                    );
                }
            }
            $body .= 'return new $mappedClass(' . $params . ');' . PHP_EOL;
        }
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
    }

    protected function getTypeAnnotation(ReflectionClass $class, $type, $uses)
    {
        $namespace = $class->getNamespaceName();
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

    public function getAnnotation()
    {
        return CollectionType::class;
    }

    protected function getParentClass(ReflectionClass $reflectedClass)
    {
        $reader = new AnnotationReader();

        $reflectedJsonCollection = new ReflectionClass(JsonCollection::class);
        $parentClassShortName = $reflectedJsonCollection->getShortName();
        $parentClassName = $reflectedJsonCollection->getName();

        $annotation = $reader->getClassAnnotation($reflectedClass, CollectionType::class);
        $interfaceParents = $reflectedClass->getInterfaceNames();
        if ($annotation instanceof CollectionType && $annotation->type) {
            $uses = $this->getUses($reflectedClass);
            $parentClassShortName = $parentClassName = $annotation->type;
            if (isset($uses[$parentClassShortName])) {
                $parentClassName = $uses[$parentClassShortName]['name'];
            }
        } elseif ($reflectedClass->isInterface() && count($interfaceParents) > 0) {
            $parentClass = new ReflectionClass(current($interfaceParents));
            $annotation = $reader->getClassAnnotation($parentClass, CollectionType::class);
            if ($annotation instanceof CollectionType) {
                $parentNamespace = $parentClass->getNamespaceName();
                $parentClassShortName = $parentClass->getShortName() . self::MODEL_SUFFIX;
                $parentClassName = $parentNamespace . '\\' . $parentClassShortName;
            }
        }

        return [$parentClassShortName, $parentClassName];
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
}
