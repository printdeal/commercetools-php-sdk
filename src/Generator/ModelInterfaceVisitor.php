<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\NodeVisitorAbstract;

class ModelInterfaceVisitor extends GeneratorVisitor
{
    private $interfaceClasses = [];


    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);

        $annotation = $this->reader->getClassAnnotation($reflectedClass, JsonResource::class);
        if ($annotation instanceof JsonResource) {
            $this->interfaceClasses[$reflectedClass->getName()] = $reflectedClass;
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
