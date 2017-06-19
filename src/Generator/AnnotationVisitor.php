<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;

class AnnotationVisitor extends NodeVisitorAbstract
{
    private $annotation;
    private $annotatedClass;
    private $reflectedClass;
    private $reader;

    public function __construct($annotation)
    {
        $this->reader = new AnnotationReader();
        $this->annotation = $annotation;
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_ && !$node instanceof Class_) {
            return null;
        }
        $annotationClass = $this->annotation;
        try {
            $reflectedClass = new \ReflectionClass((string)$node->namespacedName);
        } catch (\Exception $e) {
            var_dump($annotationClass);
            var_dump($node);
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, $annotationClass);
        if ($annotation instanceof $annotationClass) {
            $this->reflectedClass = $reflectedClass;
            $this->annotatedClass = $annotation;
        }
        return $node;
    }

    /**
     * @return mixed
     */
    public function getAnnotatedClass()
    {
        return $this->annotatedClass;
    }

    public function getReflectedClass()
    {
        return $this->reflectedClass;
    }
}
