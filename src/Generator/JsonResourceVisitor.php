<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;

class JsonResourceVisitor extends GeneratorVisitor
{
    private $resourceClasses = [];


    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);
        $annotation = $this->reader->getClassAnnotation($reflectedClass, JsonResource::class);
        if ($annotation instanceof JsonResource) {
            $this->resourceClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->resourceClasses[$reflectedClass->getName()][JsonResource::class] = $annotation;
        }
        return $node;
    }

    /**
     * @return array
     */
    public function getResourceClasses()
    {
        return $this->resourceClasses;
    }
}
