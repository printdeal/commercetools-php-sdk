<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;

class InterfaceModelVisitor extends GeneratorVisitor
{
    private $interfaceClasses = [];
    private $discriminatorClasses = [];
    private $discriminatorValueClasses = [];

    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);
        $annotation = $this->reader->getClassAnnotation($reflectedClass, JsonResource::class);
        if ($annotation instanceof JsonResource) {
            $this->interfaceClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->interfaceClasses[$reflectedClass->getName()][JsonResource::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, Discriminator::class);
        if ($annotation instanceof Discriminator) {
            $this->discriminatorClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->discriminatorClasses[$reflectedClass->getName()][Discriminator::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, DiscriminatorValue::class);
        if ($annotation instanceof DiscriminatorValue) {
            $parentInterface = current($reflectedClass->getInterfaceNames());
            $this->discriminatorValueClasses[$parentInterface][$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->discriminatorValueClasses[$parentInterface][$reflectedClass->getName()][DiscriminatorValue::class] = $annotation;
        }
        return $node;
    }

    /**
     * @return array
     */
    public function getInterfaceClasses()
    {
        return $this->interfaceClasses;
    }
}
