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

        $annotation = $this->reader->getClassAnnotation($reflectedClass, DraftableCollection::class);

        if ($annotation instanceof DraftableCollection) {
            $annotation = $this->reader->getClassAnnotation($reflectedClass, CollectionType::class);

            if ($annotation instanceof CollectionType) {
                if (!$this->findMethod($node, 'add')) {
                    $node->stmts[] = $this->getCollectionAdd($annotation);
                }
            }
        }
    }

    private function getCollectionAdd(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('add')
            ->makePublic()
            ->addParam($factory->param('value'))
//            ->addStmt(
//                new Node\Expr\Assign(
//                    new Node\Expr\Variable('this->' . $property->getName()),
//                    new Node\Expr\Variable($property->getName())
//                )
//            )
            ->getNode();
        
        return $method;
    }

    private function getCollectionCurrent(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('current')
            ->makePublic()
            ->setDocComment('/**
                               * @return ' . $annotation->type . '
                               */')
            ->getNode();

        $body = '    return parent::current();';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }
}
