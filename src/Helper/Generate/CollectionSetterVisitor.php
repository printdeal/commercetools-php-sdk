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

class CollectionSetterVisitor extends GeneratorVisitor
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

        $annotation = $this->reader->getClassAnnotation($reflectedClass, CollectionSetter::class);

        if ($annotation instanceof CollectionSetter) {
            if ($annotation->type == 'map') {
                if (!$this->findMethod($node, 'set')) {
                    $node->stmts[] = $this->getCollectionSet($annotation);
                }
            }
            if ($annotation->type == 'list') {
                if (!$this->findMethod($node, 'add')) {
                    $node->stmts[] = $this->getCollectionAdd();
                }
            }
        }
    }

    private function getCollectionSet()
    {
        $factory = new BuilderFactory();

        $body =    '    if ($value instanceof ArraySerializable) {' . PHP_EOL;
        $body.=    '        $value = $value->toArray();' . PHP_EOL;
        $body.=    '    }' . PHP_EOL;
        $body.=    '    $this->index([$key => $value]);' . PHP_EOL;
        $body.=    '    $this->rawSet($key, $value);' . PHP_EOL;
        $stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        $method = $factory->method('set')
            ->makePublic()
            ->addParam($factory->param('key'))
            ->addParam($factory->param('value'))
            ->addStmts($stmts)
            ->getNode();

        return $method;
    }

    private function getCollectionAdd()
    {
        $factory = new BuilderFactory();

        $body =    '    if (!$value instanceof ' . PHP_EOL;
        $body =    '    if ($value instanceof ArraySerializable) {' . PHP_EOL;
        $body.=    '        $value = $value->toArray();' . PHP_EOL;
        $body.=    '    }' . PHP_EOL;
        $body.=    '    $this->index([$this->count() => $value]);' . PHP_EOL;
        $body.=    '    $this->rawSet(null, $value);' . PHP_EOL;
        $stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        $method = $factory->method('add')
            ->makePublic()
            ->addParam($factory->param('value'))
            ->addStmts($stmts)
            ->getNode();
        
        return $method;
    }
}
