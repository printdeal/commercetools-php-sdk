<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Generator;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use ReflectionClass;

class DiscriminatorProcessor extends AbstractProcessor
{
    private $path;
    private $outputPath;
    private $discriminatorValueClasses = [];

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

    /**
     * @inheritDoc
     */
    public function process(ReflectionClass $class, $annotation)
    {
        $parentInterface = current($class->getInterfaceNames());
        $this->discriminatorValueClasses[$parentInterface][$class->getName()][\ReflectionClass::class] = $class;
        $this->discriminatorValueClasses[$parentInterface][$class->getName()][DiscriminatorValue::class] = $annotation;

        $reflectedClass = new ReflectionClass($parentInterface);
        $factory = new BuilderFactory();
        $builder = $factory->namespace($reflectedClass->getNamespaceName());

        $modelPath = str_replace($this->path, $this->outputPath, dirname($reflectedClass->getFileName()));

        $className = $reflectedClass->getShortName() . 'DiscriminatorResolver';
        $classBuilder = $factory->class($className);
        $types = [];
        /**
         * @var DiscriminatorValue $discriminatorValue
         */
        $classValues = $this->discriminatorValueClasses[$parentInterface];
        foreach ($classValues as $valueClass => $discriminatorValue) {
            /**
             * @var ReflectionClass $reflectedValueClass
             */
            $reflectedValueClass = $discriminatorValue[ReflectionClass::class];
            $types[] = new Expr\ArrayItem(
                new Expr\ClassConstFetch(
                    new Node\Name($reflectedValueClass->getShortName()),
                    'class'
                ),
                new Scalar\String_($discriminatorValue[DiscriminatorValue::class]->value)
            );
            $builder->addStmt($factory->use($reflectedValueClass->getName()));
        }
        $classBuilder->addStmt(new Stmt\ClassConst([
            new Node\Const_('TYPES', new Expr\Array_($types, ['kind' => Expr\Array_::KIND_SHORT]))
        ]));
        $classBuilder->addStmt($this->getDiscriminatorResolverMethod($reflectedClass));
        $builder->addStmt($classBuilder);

        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(
            new NamespaceChangeVisitor($reflectedClass->getNamespaceName(), $reflectedClass->getNamespaceName())
        ); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        $this->writeClass($fileName, $stmts);

        return [];
    }

    public function getDiscriminatorResolverMethod(\ReflectionClass $reflectionClass)
    {
        $factory = new BuilderFactory();
        $method = $factory->method('discriminatorType')
            ->makePublic()
            ->makeStatic()
            ->addParam(
                $factory->param('data')->setTypeHint('array')
            )
            ->addParam(
                $factory->param('discriminatorName')
            )
            ->getNode();
        $modelName = $reflectionClass->getShortName() . static::MODEL_SUFFIX;
        $body = '$types = static::TYPES;
    $discriminator = isset($data[$discriminatorName]) ? $data[$discriminatorName] : \'\';
    return isset($types[$discriminator]) ? $types[$discriminator] : ' . $modelName . '::class;';
        $method->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
        return $method;
    }

    public function getAnnotation()
    {
        return DiscriminatorValue::class;
    }
}
