<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use Commercetools\Core\Templates\Common\JsonObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use PhpParser\BuilderFactory;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionProperty;

class PropertyVisitor extends NodeVisitorAbstract
{
    private $reader;

    private $namespace;
    private $uses;

    public function __construct(array $annotationClasses)
    {
        foreach ($annotationClasses as $annotationClass) {
            $class = new ReflectionClass($annotationClass);
            AnnotationRegistry::registerFile(
                $class->getFileName()
            );
        }
        $this->reader = new AnnotationReader();
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = $node->name->toString();
        }
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $this->uses[$use->alias] = $use->name->toString();
            }
        }
    }

    /**
     * @param Node $node
     *
     * @return null|Node\Stmt\Class_
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }
        if ($node->namespacedName == JsonObject::class) {
            return null;
        }

        $reflectedClass = new ReflectionClass((string)$node->namespacedName);

        $annotation = $this->reader->getClassAnnotation($reflectedClass, CollectionType::class);

        if ($annotation instanceof CollectionType) {
            $node->stmts[] =$this->getCollectionAt($annotation);
            $node->stmts[] =$this->getCollectionCurrent($annotation);
            if (!empty($annotation->indexes)) {
                $node->stmts[] = $this->getCollectionIndexer($annotation);

                foreach ($annotation->indexes as $index) {
                    $node->stmts[] = $this->getCollectionIndexGetter($annotation, $index);
                }
            }
        }

        $accessibleProperties = $this->getProtectedProperties($reflectedClass);
        foreach ($accessibleProperties as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, FieldType::class);
            if (!$annotation instanceof FieldType) {
                continue;
            }

            $methodName =  'get'.ucfirst($property->getName());
            if (!$this->findMethod($node, $methodName)) {
                $node->stmts[] = $this->getPropertyGetter($property, $annotation);
            }
        }

        return $node;
    }

    private function getCollectionIndexer(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('index')
            ->makePublic()
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

    private function getCollectionIndexGetter(CollectionType $annotation, $index)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('by' . ucfirst($index))
            ->makePublic()
            ->addParam($factory->param($index))
            ->setDocComment('/**
                               * @return ' . $annotation->type . '
                               */')
            ->getNode();

        $body = '    return $this->valueByKey(\'' . $index . '\', $' . $index . ');';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }

    private function getCollectionAt(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('at')
            ->makePublic()
            ->addParam($factory->param('index'))
            ->getNode();

        if ($typeAnnotation = $this->getTypeAnnotation($annotation->type)) {
            $body = '    $data = $this->raw($index);' . PHP_EOL;
            $body.= '    $type = ' . $typeAnnotation->callback . '($data, \'' . $typeAnnotation->name . '\');';
            $body.= '    return new $type($data);' . PHP_EOL;
        } else {
            $body = '    return new ' . $annotation->type . '($this->raw($index));';
        }
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }

    private function getCollectionCurrent(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('current')
            ->makePublic()
            ->setDocComment('/**
                               * @return ' . $annotation->type . '
                               */')
            ->getNode();

        $body = '    return parent::current();';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }

    private function getPropertyGetter(ReflectionProperty $property, FieldType $annotation)
    {
        $methodName =  'get'.ucfirst($property->getName());
        $factory = new BuilderFactory();
        $method = $factory->method($methodName)
            ->makePublic()
            ->getNode();

        $body = 'if (is_null($this->'. $property->getName() . ') &&
    !is_null($value = $this->raw(\'' . $property->getName() . '\'))) {';

        if (is_null($annotation->type)) {
            $body .= '    $this->' . $property->getName() .
                ' = $value;' . PHP_EOL;
        } elseif (in_array($annotation->type, ['int', 'bool', 'string', 'float', 'array'])) {
            $body .= '    $this->' . $property->getName() .
                ' = (' . $annotation->type . ')$value;' . PHP_EOL;
        } else {
            $params = '$value';
            if ($annotation->params) {
                foreach ($annotation->params as $param) {
                    $methodName =  'get'.ucfirst($param);
                    $params .= ', $this->' . $methodName . '()';
                }
            }
            if ($typeAnnotation = $this->getTypeAnnotation($annotation->type)) {
                $body .= '    $type = ' . $typeAnnotation->callback . '($value, \'' . $typeAnnotation->name . '\');';
                $body .= '    $this->' . $property->getName() . ' = new $type(' . $params . ');' . PHP_EOL;
            } else {
                $body .= '    $this->' . $property->getName() .
                    ' = new '.$annotation->type.'(' . $params . ');' . PHP_EOL;
            }
        }
        $body .= '}' . PHP_EOL . 'return $this->' . $property->getName() . ';' . PHP_EOL;

        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }

    private function getTypeAnnotation($type)
    {
        $typeAnnotation = false;
        if (isset($this->uses[$type])) {
            $typeClass = new ReflectionClass($this->uses[$type]);
            $typeAnnotation = $this->reader->getClassAnnotation($typeClass, DiscriminatorColumn::class);
        } elseif (class_exists($this->namespace . '\\' . $type)) {
            $typeClass = new ReflectionClass($this->namespace . '\\' . $type);
            $typeAnnotation = $this->reader->getClassAnnotation($typeClass, DiscriminatorColumn::class);
        }
        return $typeAnnotation;
    }

    /**
     *  creates a class method
     *
     * @param Class_ $class
     * @param string                    $name  name of the method
     *
     * @return ClassMethod
     */
    private function createMethod(Class_ $class, $name)
    {
        $class->stmts[] = $method = new ClassMethod($name);
        $method->type = $method->type | Class_::MODIFIER_PUBLIC;
        return $method;
    }

    private function findMethod(Class_ $class, $name)
    {
        $foundMethods = array_filter(
            $class->getMethods(),
            function (ClassMethod $method) use ($name) {
                return $name === $method->name;
            }
        );
        $method = reset($foundMethods);

        return $method;
    }

    private function findOrCreateMethod(Class_ $class, $name)
    {
        $method = $this->findMethod($class, $name);
        if (!$method) {
            $method = $this->createMethod($class, $name);
        }
        return $method;
    }

    /**
     * Retrieve instance public/protected properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    private function getProtectedProperties(ReflectionClass $reflectedClass)
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) {
                return !$property->isStatic();
            }
        );
    }
}
