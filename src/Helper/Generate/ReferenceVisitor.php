<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use Commercetools\Core\Templates\Common\JsonObject;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use ReflectionClass;

class ReferenceVisitor extends GeneratorVisitor
{
    /**
     * @param Node $node
     *
     * @return null|Node\Stmt\Class_
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }
        if ($node->namespacedName == JsonObject::class) {
            return null;
        }

        $reflectedClass = new ReflectionClass((string)$node->namespacedName);

        $annotation = $this->reader->getClassAnnotation($reflectedClass, ReferenceType::class);

        if ($annotation instanceof ReferenceType) {
            array_unshift(
                $node->stmts,
                new Node\Stmt\Const_([
                    new Node\Const_('REFERENCE_TYPE_ID', new Node\Scalar\String_($annotation->type))
                ])
            );
        }
    }
}
