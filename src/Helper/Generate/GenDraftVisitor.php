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

class GenDraftVisitor extends GeneratorVisitor
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

        $annotation = $this->reader->getClassAnnotation($reflectedClass, Draftable::class);

        if ($annotation instanceof Draftable) {
            var_dump($node->stmts[0]);
        }

        return $node;
    }

    private function getPropertyGetter(ReflectionProperty $property, JsonField $annotation)
    {
        $methodName =  'get'.ucfirst($property->getName());
        $factory = new BuilderFactory();
        $method = $factory->method($methodName)
            ->makePublic()
            ->getNode();

        $body = 'if (is_null($this->'. $property->getName() . ') &&
    !is_null($value = $this->raw(\'' . $property->getName() . '\'))) {';

        if (is_null($annotation->type)) {
            $body .= '    $this->' . $property->getName() .
                ' = $value;' . PHP_EOL;
        } elseif (in_array($annotation->type, ['int', 'bool', 'string', 'float', 'array'])) {
            $body .= '    $this->' . $property->getName() .
                ' = (' . $annotation->type . ')$value;' . PHP_EOL;
        } else {
            $params = '$value';
            if ($annotation->params) {
                foreach ($annotation->params as $param) {
                    $methodName =  'get'.ucfirst($param);
                    $params .= ', $this->' . $methodName . '()';
                }
            }
            if ($typeAnnotation = $this->getTypeAnnotation($annotation->type)) {
                $body .= '    $type = ' . $typeAnnotation->callback . '($value, \'' . $typeAnnotation->name . '\');';
                $body .= '    $this->' . $property->getName() . ' = new $type(' . $params . ');' . PHP_EOL;
            } else {
                $body .= '    $this->' . $property->getName() .
                    ' = new '.$annotation->type.'(' . $params . ');' . PHP_EOL;
            }
        }
        $body .= '}' . PHP_EOL . 'return $this->' . $property->getName() . ';' . PHP_EOL;

        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
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
