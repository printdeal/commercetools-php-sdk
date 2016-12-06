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

class CollectionVisitor extends GeneratorVisitor
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

        $annotation = $this->reader->getClassAnnotation($reflectedClass, CollectionType::class);

        if ($annotation instanceof CollectionType) {
            if (!$this->findMethod($node, 'at')) {
                $node->stmts[] =$this->getCollectionAt($annotation);
            }
            if (!$this->findMethod($node, 'current')) {
                $node->stmts[] =$this->getCollectionCurrent($annotation);
            }
            if (!empty($annotation->indexes)) {
                if (!$this->findMethod($node, 'index')) {
                    $node->stmts[] = $this->getCollectionIndexer($annotation);
                }

                foreach ($annotation->indexes as $index) {
                    if (!$this->findMethod($node, 'by' . ucfirst($index))) {
                        $node->stmts[] = $this->getCollectionIndexGetter($annotation, $index);
                    }
                }
            }
        }
    }
    
    private function getCollectionIndexer(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('index')
            ->makeProtected()
            ->addParam($factory->param('data'))
            ->getNode();

        $body = '    foreach ($data as $key => $value) {' . PHP_EOL;
        foreach ($annotation->indexes as $index) {
            $body.= '        if (isset($value[\'' . $index . '\'])) {' . PHP_EOL;
            $body.= '            $this->addToIndex(\'' . $index . '\', $value[\'' . $index . '\'], $key);' . PHP_EOL;
            $body.= '        }' . PHP_EOL;
        }
        $body.= '    }' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }

    private function getCollectionIndexGetter(CollectionType $annotation, $index)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('by' . ucfirst($index))
            ->makePublic()
            ->addParam($factory->param($index))
            ->setDocComment('/**
                               * @return ' . $annotation->type . '
                               */')
            ->getNode();

        $body = '    return $this->valueByKey(\'' . $index . '\', $' . $index . ');';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        return $method;
    }

    private function getCollectionAt(CollectionType $annotation)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('at')
            ->makePublic()
            ->addParam($factory->param('index'))
            ->getNode();

        $body =    '    if (!isset($this->data[$index])) {' . PHP_EOL;
        $body.=    '        $data = $this->raw($index);' . PHP_EOL;
        $body.=    '        if (!is_null($data)) {' . PHP_EOL;
        if ($typeAnnotation = $this->getTypeAnnotation($annotation->type)) {
            $body.='            $type = ' . $typeAnnotation->callback . '($data, \'' . $typeAnnotation->name . '\');' .
                PHP_EOL;
            $body.='            $data = new $type($data);' . PHP_EOL;
        } else {
            $body.='            $data = new ' . $annotation->type . '($data);' . PHP_EOL;
        }
        $body.=    '        }' . PHP_EOL;
        $body.=    '        $this->data[$index] = $data;' . PHP_EOL;
        $body.=    '    }' . PHP_EOL;
        $body.=    '    return $this->data[$index];' . PHP_EOL;
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

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
