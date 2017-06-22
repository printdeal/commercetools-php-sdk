<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\Collection;
use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use ReflectionClass;

class CollectableProcessor extends AbstractProcessor
{
    private $path;
    private $outputPath;

    /**
     * ResourceProcessor constructor.
     * @param $path
     * @param $outputPath
     */
    public function __construct($path, $outputPath)
    {
        $this->path = $path;
        $this->outputPath = $outputPath;
    }


    public function getAnnotation()
    {
        return Collectable::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ReflectionClass $class, $annotation)
    {
        if (!$annotation instanceof Collectable) {
            return [];
        }
        $factory = new BuilderFactory();
        $builder = $factory->namespace($class->getNamespaceName());

        $modelPath = str_replace($this->path, $this->outputPath, dirname($class->getFileName()));

        $collection = new ReflectionClass(Collection::class);
        $className = $class->getShortName() . $collection->getShortName();

        $classBuilder = $factory->interface($className)->extend($collection->getShortName());
        if (count($annotation->indexes) > 0) {
            $comment =
                '/**' . PHP_EOL .
                '  * @CollectionType(elementType="' . $class->getShortName() . '",' .
                'indexes={"' . implode('""', $annotation->indexes) . '"})' . PHP_EOL .
                '  */';
            $classBuilder->setDocComment($comment);
        } else {
            $classBuilder->setDocComment(
                '/**
                  * @CollectionType(elementType="' . $class->getShortName() . '")
                  */'
            );
        }
        foreach ($annotation->indexes as $index) {
            $methodBuilder = $factory->method('by' . ucfirst($index))
                ->makePublic()
                ->setDocComment('/**
                              * @param $' . $index . '
                              * @return ' . $class->getShortName() . '
                              */')
                ->addParam($factory->param($index));
            $classBuilder->addStmt($methodBuilder);
        }
        $methodBuilder = $factory->method('at')
            ->makePublic()
            ->setDocComment('/**
                              * @return ' . $class->getShortName() . '
                              */')
            ->addParam($factory->param('index'));
        $classBuilder->addStmt($methodBuilder);

        $methodBuilder = $factory->method('current')
            ->makePublic()
            ->setDocComment('/**
                              * @return ' . $class->getShortName() . '
                              */');
        $classBuilder->addStmt($methodBuilder);

        $builder->addStmt($factory->use(CollectionType::class));
        $builder->addStmt($factory->use(Collection::class));
        $builder->addStmt($classBuilder);

        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(
            new NamespaceChangeVisitor($class->getNamespaceName(), $class->getNamespaceName())
        ); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        return [$this->writeClass($fileName, $stmts)];
    }
}
