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

class JsonFieldSetterVisitor extends GeneratorVisitor
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

        $accessibleProperties = $this->getProtectedProperties($reflectedClass);
        foreach ($accessibleProperties as $property) {
            $annotation = $this->reader->getPropertyAnnotation($property, JsonFieldSetter::class);
            if (!$annotation instanceof JsonFieldSetter) {
                continue;
            }

            $methodName =  'set'.ucfirst($property->getName());
            if (!$this->findMethod($node, $methodName)) {
                $node->stmts[] = $this->getPropertySetter($property, $annotation);
            }
        }

        return $node;
    }

    public function getPropertySetter(ReflectionProperty $property, JsonFieldSetter $annotation)
    {
        $methodName =  'set'.ucfirst($property->getName());
        $factory = new BuilderFactory();
        $param = $factory->param($property->getName());
        $cast = '';
        if ($annotation->type) {
            if (!in_array($annotation->type, ['int', 'string', 'float', 'bool'])) {
                $param->setTypeHint($annotation->type);
            } else {
                $cast = '(' . $annotation->type . ')';
            }
        }
        $docComment = '';
        if ($annotation->paramTypes) {
            $docComment = '/**' . PHP_EOL . ' *' . PHP_EOL;
            $docComment .=' * @param ' . implode('|', $annotation->paramTypes) . ' $' . $property->getName() . PHP_EOL;
            $docComment .=' */';
        }

        $body =    '    $this->' . $property->getName() .' = ' . $cast . '$' . $property->getName() . ';' . PHP_EOL;
        $stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        $method = $factory->method($methodName)
            ->addParam($param)
            ->makePublic()
            ->setDocComment($docComment)
            ->addStmts($stmts)
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
