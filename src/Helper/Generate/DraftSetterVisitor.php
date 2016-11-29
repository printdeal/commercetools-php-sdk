<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use Commercetools\Core\Templates\Common\JsonObject;
use PhpParser\BuilderFactory;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionProperty;

class DraftSetterVisitor extends GeneratorVisitor
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

        $draft = $this->reader->getClassAnnotation($reflectedClass, Draftable::class);
        if (!$draft instanceof Draftable) {
            return null;
        }
        $accessibleProperties = $this->getProtectedProperties($reflectedClass);
        foreach ($accessibleProperties as $property) {
            if (!in_array($property->getName(), $draft->fields)) {
                continue;
            }
            $annotation = $this->reader->getPropertyAnnotation($property, JsonField::class);
            if (!$annotation instanceof JsonField) {
                continue;
            }

            $methodName =  'set'.ucfirst($property->getName());
            if (!$this->findMethod($node, $methodName)) {
                $node->stmts[] = $this->getPropertySetter($property, $annotation);
            }
        }

        return $node;
    }

    public function getPropertySetter(ReflectionProperty $property, JsonField $annotation)
    {
        $methodName =  'set'.ucfirst($property->getName());
        $factory = new BuilderFactory();
        $method = $factory->method($methodName)
            ->addParam($factory->param($property->getName()))
            ->makePublic()
            ->addStmt(
                new Node\Expr\Assign(
                    new Node\Expr\Variable('this->' . $property->getName()),
                    new Node\Expr\Variable($property->getName())
                )
            )
            ->getNode();

        return $method;
    }
    

    /**
     * Retrieve instance public/protected properties
     *
     * @param ReflectionClass $reflectedClass
     *
     * @return ReflectionProperty[]
     */
    public function getProtectedProperties(ReflectionClass $reflectedClass)
    {
        return array_filter(
            $reflectedClass->getProperties(),
            function (ReflectionProperty $property) {
                return !$property->isStatic();
            }
        );
    }
}
