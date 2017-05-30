<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Generator;

use Commercetools\Core\Model\Common\JsonObject;
use Commercetools\Generator\JsonField;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
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
        if (!$node instanceof Interface_) {
            return null;
        }
        if ($node->namespacedName == JsonObject::class) {
            return null;
        }
        $reflectedClass = new ReflectionClass((string)$node->namespacedName);
        foreach ($reflectedClass->getMethods() as $reflectionMethod) {
            $annotation = $this->reader->getMethodAnnotation($reflectionMethod, JsonField::class);
            if (!$annotation instanceof JsonField) {
                continue;
            }
            if (!in_array($annotation->type, ['int', 'bool', 'string', 'float', 'array'])) {
                $this->propertyTypes[$reflectionMethod->getName()] = $annotation->type;
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
