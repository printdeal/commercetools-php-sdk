<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\Collection;
use Commercetools\Model\Reference;
use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use ReflectionClass;

class ReferenceableProcessor extends AbstractProcessor
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
        return Referenceable::class;
    }

    /**
     * @inheritDoc
     */
    public function process(ReflectionClass $class, $annotation)
    {
        if (!$annotation instanceof Referenceable) {
            return [];
        }
        $factory = new BuilderFactory();
        $builder = $factory->namespace($class->getNamespaceName());

        $modelPath = str_replace($this->path, $this->outputPath, dirname($class->getFileName()));

        $reference = new ReflectionClass(Reference::class);
        $className = $class->getShortName() . $reference->getShortName();
        $classBuilder = $factory->interface($className)->extend($reference->getShortName());
        $typeId = $annotation->typeId;
        if (empty($typeId)) {
            $typeId = $this->camel2dashed($class->getShortName());
        }
        $classBuilder->setDocComment(
            '/**
              * @JsonResource()
              * @Collectable()
              * @DiscriminatorValue(value="' . $typeId . '")
              */'
        );
        $classBuilder->addStmt(
            $factory->method('getObj')->makePublic()->setDocComment(
                '/**
                  * @JsonField(type="' . $class->getShortName() . '")
                  * @return ' . $class->getShortName() . '
                  */'
            )
        );
        $builder->addStmt($factory->use(JsonField::class));
        $builder->addStmt($factory->use(Collectable::class));
        $builder->addStmt($factory->use(JsonResource::class));
        $builder->addStmt($factory->use(DiscriminatorValue::class));
        $builder->addStmt($factory->use(Reference::class));
        $builder->addStmt($classBuilder);

        $node = $builder->getNode();
        $stmts = [$node];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor(new NamespaceChangeVisitor($class->getNamespaceName(), $class->getNamespaceName())); // we will shorten the resolved names
        $traverser->traverse($stmts);

        $fileName = $modelPath . '/' . $className . '.php';
        return [$this->writeClass($fileName, $stmts)];
    }

    private function camel2dashed($string)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
    }
}
