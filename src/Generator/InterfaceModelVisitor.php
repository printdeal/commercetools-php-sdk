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


    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);
        $this->interfaceClasses[$reflectedClass->getName()]['class'] = $reflectedClass;
        $annotation = $this->reader->getClassAnnotation($reflectedClass, JsonResource::class);
        if ($annotation instanceof JsonResource) {
            $this->interfaceClasses[$reflectedClass->getName()][JsonResource::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, Discriminator::class);
        if ($annotation instanceof Discriminator) {
            $this->interfaceClasses[$reflectedClass->getName()][Discriminator::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, DiscriminatorValue::class);
        if ($annotation instanceof DiscriminatorValue) {
            $this->interfaceClasses[$reflectedClass->getName()][DiscriminatorValue::class] = $annotation;
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
