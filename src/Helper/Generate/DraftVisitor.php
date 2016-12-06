<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use ReflectionClass;

class DraftVisitor extends GeneratorVisitor
{
    private $draftableClasses = [];

    public function enterNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }
        $reflectedClass = new ReflectionClass((string)$node->namespacedName);

        $annotation = $this->reader->getClassAnnotation($reflectedClass, Draftable::class);

        if ($annotation instanceof Draftable) {
            $this->draftableClasses[$reflectedClass->getName()] = $reflectedClass;
            return null;
        }

        $annotation = $this->reader->getClassAnnotation($reflectedClass, DraftableCollection::class);

        if ($annotation instanceof DraftableCollection) {
            $this->draftableClasses[$reflectedClass->getName()] = $reflectedClass;
            return null;
        }
    }

    public function getDraftableClasses()
    {
        return $this->draftableClasses;
    }
}
