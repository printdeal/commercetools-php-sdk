<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\ClassMap;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use ReflectionClass;

class ClassMapProcessor extends AbstractProcessor
{
    private $namespace;
    private $outputPath;

    public function __construct($namespace, $outputPath)
    {
        $this->namespace = $namespace;
        $this->outputPath = $outputPath;
    }

    public function getAnnotations()
    {
        return [JsonResource::class];
    }

    /**
     * @param $annotationResult
     */
    public function process()
    {
        $factory = new BuilderFactory();
        $builder = $factory->namespace($this->namespace);
        $builder->addStmt($factory->use(ClassMap::class));
        $classBuilder = $factory->class('ResourceModelClassMap')->extend('ClassMap');

        $types = [];
        foreach ($this->getResult(JsonResource::class) as $className => $resourceClass) {
            /**
             * @var ReflectionClass $reflectedClass
             */
            $reflectedClass = $resourceClass[ReflectionClass::class];
            $types[] = new Expr\ArrayItem(
                new Expr\ClassConstFetch(
                    new Node\Name('\\' . $reflectedClass->getName() . static::MODEL_SUFFIX), 'class'
                ),
                new Expr\ClassConstFetch(
                    new Node\Name('\\' . $reflectedClass->getName()), 'class'
                )
            );
        }
        $classBuilder->addStmt(
            $factory->property('types')->makeProtected()->makeStatic()->setDefault(
                new Expr\Array_($types, ['kind' => Expr\Array_::KIND_SHORT])
            )
        );
        $builder->addStmt($classBuilder);

        $fileName = $this->outputPath . '/ResourceModelClassMap.php';
        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(new NamespaceChangeVisitor($this->namespace, $this->namespace)); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $this->writeClass($fileName, $stmts);
    }
}
