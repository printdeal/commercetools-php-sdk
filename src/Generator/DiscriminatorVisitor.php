<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node;

class DiscriminatorVisitor extends GeneratorVisitor
{
    private $discriminatorClasses = [];


    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);

        $annotation = $this->reader->getClassAnnotation($reflectedClass, Discriminator::class);
        if ($annotation instanceof Discriminator) {
            $this->discriminatorClasses[$reflectedClass->getName()] = $annotation;
        }
        return $node;
    }

    /**
     * @return array
     */
    public function getDiscriminatorClasses()
    {
        return $this->discriminatorClasses;
    }
}
