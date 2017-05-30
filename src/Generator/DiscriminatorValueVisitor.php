<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node;

class DiscriminatorValueVisitor extends GeneratorVisitor
{
    private $discriminatorValues = [];


    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);

        $annotation = $this->reader->getClassAnnotation($reflectedClass, DiscriminatorValue::class);
        if ($annotation instanceof DiscriminatorValue) {
            $parentInterface = current($reflectedClass->getInterfaceNames());
            $this->discriminatorValues[$parentInterface][$reflectedClass->getName()] = $annotation;
        }
        return $node;
    }

    /**
     * @return array
     */
    public function getDiscriminatorValues()
    {
        return $this->discriminatorValues;
    }
}
