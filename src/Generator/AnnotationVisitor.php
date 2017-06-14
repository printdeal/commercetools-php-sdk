<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;

class AnnotationVisitor extends NodeVisitorAbstract
{
    private $annotation;
    private $annotatedClasses;
    private $reader;

    public function __construct($annotation)
    {
        $this->reader = new AnnotationReader();
        $this->annotation = $annotation;
    }

    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_ && !$node instanceof Node\Stmt\Class_) {
            return null;
        }
        $annotationClass = $this->annotation;
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);
        $annotation = $this->reader->getClassAnnotation($reflectedClass, $annotationClass);
        if ($annotation instanceof DiscriminatorValue) {
            $parentInterface = current($reflectedClass->getInterfaceNames());
            $this->annotatedClasses[$parentInterface][$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->annotatedClasses[$parentInterface][$reflectedClass->getName()][DiscriminatorValue::class] = $annotation;
        } else {
            if ($annotation instanceof $annotationClass) {
                $this->annotatedClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
                $this->annotatedClasses[$reflectedClass->getName()][JsonResource::class] = $annotation;
            }
        }
        return $node;
    }

    /**
     * @return mixed
     */
    public function getAnnotatedClasses()
    {
        return $this->annotatedClasses;
    }
}
