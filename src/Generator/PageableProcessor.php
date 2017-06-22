<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\Collection;
use Commercetools\Model\PagedQueryResult;
use Commercetools\Model\Reference;
use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use ReflectionClass;

class PageableProcessor extends AbstractProcessor
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
        return Pageable::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ReflectionClass $class, $annotation)
    {
        if (!$annotation instanceof Pageable) {
            return [];
        }
        $factory = new BuilderFactory();
        $builder = $factory->namespace($class->getNamespaceName());

        $modelPath = str_replace($this->path, $this->outputPath, dirname($class->getFileName()));

        $reference = new ReflectionClass(PagedQueryResult::class);
        $collection = new ReflectionClass(Collection::class);
        $className = $class->getShortName() . $reference->getShortName();
        $classBuilder = $factory->interface($className)->extend($reference->getShortName());
        $classBuilder->setDocComment(
            '/**
              * @JsonResource()
              */'
        );
        $classBuilder->addStmt(
            $factory->method('getResults')->makePublic()->setDocComment(
                '/**
                  * @JsonField(type="' . $class->getShortName() . $collection->getShortName() . '")
                  * @return ' . $class->getShortName() . $collection->getShortName() . '
                  */'
            )
        );
        $builder->addStmt($factory->use(JsonField::class));
        $builder->addStmt($factory->use(JsonResource::class));
        $builder->addStmt($factory->use(PagedQueryResult::class));
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

    private function camel2dashed($string)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
