<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Ctp\Generator;

use Ctp\Core\Model\Common\JsonObject;
use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;

class UseVisitor extends NodeVisitorAbstract
{
    protected $propertyTypes = [];
    protected $uses = [];
    private $reader;

    public function __construct()
    {
        $this->reader = new AnnotationReader();
    }

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
     * @return null|Node\Stmt\Class_|Interface_
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Interface_ || $node instanceof Class_) {
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
}
