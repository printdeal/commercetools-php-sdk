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
    private $reflectedClasses = [];

    public function __construct($namespace, $outputPath)
    {
        $this->namespace = $namespace;
        $this->outputPath = $outputPath;
    }

    public function getAnnotation()
    {
        return [JsonResource::class, CollectionType::class];
    }

    public function process(ReflectionClass $class, $annotation)
    {
        $this->reflectedClasses[$class->getName()] = $class;
        $factory = new BuilderFactory();
        $builder = $factory->namespace($this->namespace);
        $classBuilder = $factory->class('ResourceModelClassMap')->extend('ClassMap');

        $types = [];
        /**
         * @var ReflectionClass $reflectedClass
         */
        foreach ($this->reflectedClasses as $reflectedClass) {
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

        return [];
    }
}
