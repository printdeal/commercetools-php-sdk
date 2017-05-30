<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

class GeneratorVisitor extends NodeVisitorAbstract
{
    protected $reader;
    protected $namespace;
    protected $uses;
    public function __construct()
    {
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
    protected function getTypeAnnotation($type)
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
     * @param string $name  name of the method
     *
     * @return ClassMethod
     */
    protected function createMethod(Class_ $class, $name)
    {
        $class->stmts[] = $method = new ClassMethod($name);
        $method->type = $method->type | Class_::MODIFIER_PUBLIC;
        return $method;
    }

    protected function findMethod(Class_ $class, $name)
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

    protected function findOrCreateMethod(Class_ $class, $name)
    {
        $method = $this->findMethod($class, $name);
        if (!$method) {
            $method = $this->createMethod($class, $name);
        }
        return $method;
    }
}
