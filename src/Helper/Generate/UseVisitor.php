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
use ReflectionProperty;

class UseVisitor extends GeneratorVisitor
{
    protected $propertyTypes = [];
    protected $uses = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Use_) {
            foreach ($node->uses as $use) {
                $data = ['name' => $use->name->toString()];
                if ($use->name->getLast() != $use->alias) {
                    $data['alias'] = $use->alias;
                }
                $this->uses[$use->alias] = $data;
            }
        }
    }

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

        $accessibleProperties = $this->getProtectedProperties($reflectedClass);
        foreach ($accessibleProperties as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, JsonField::class);
            if (!$annotation instanceof JsonField) {
                continue;
            }
            if (!in_array($annotation->type, ['int', 'bool', 'string', 'float', 'array'])) {
                $this->propertyTypes[$property->getName()] = $annotation->type;
            }
        }

        return $node;
    }

    public function getUses()
    {
        return $this->uses;
    }

    public function getPropertyTypes()
    {
        return $this->propertyTypes;
    }

    /**
     * Retrieve instance public/protected properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    private function getProtectedProperties(ReflectionClass $reflectedClass)
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) {
                return !$property->isStatic();
            }
        );
    }
}
