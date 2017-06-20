<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Commercetools\Model\JsonCollection;
use Commercetools\Model\ResourceClassMap;
use Doctrine\Common\Annotations\AnnotationReader;
use PhpParser\BuilderFactory;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use ReflectionClass;

class CollectionTypeProcessor extends AbstractProcessor
{
    private $path;
    private $outputPath;

    /**
     * @param $namespace
     * @param $path
     * @param $outputPath
     */
    public function __construct($path, $outputPath)
    {
        $this->path = $path;
        $this->outputPath = $outputPath;
    }

    public function process(ReflectionClass $class, $annotation)
    {
        if (!$annotation instanceof CollectionType) {
            return [];
        }
        $factory = new BuilderFactory();

        $builder = $factory->namespace($class->getNamespaceName());

        $classUses[$class->getName()] = $factory->use($class->getName());
        $classUses[] = $factory->use(ResourceClassMap::class);

        $modelPath = str_replace($this->path, $this->outputPath, dirname($class->getFileName()));

        $className = $class->getShortName() . static::MODEL_SUFFIX;
        $classBuilder = $factory->class($className)
            ->implement($class->getShortName());

        list($parentClassShortName, $parentClass) = $this->getParentClass($class);
        $classUses[$parentClassShortName] = $factory->use($parentClass);
        $classBuilder = $classBuilder->extend($parentClassShortName);

        $atMethod = $factory->method('map')
            ->addParam($factory->param('data'))
            ->makeProtected()
            ->getNode();
        $body = 'return $data;';
        $atMethod->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);

        $classBuilder->addStmt($atMethod);

        foreach ($annotation->indexes as $index) {
            $byMethod = $factory->method('by' . ucfirst($index))
                ->addParam($factory->param($index))
                ->makePublic()
                ->getNode();
            $body = 'return $this->valueByKey(\'' . $index . '\', $' . $index . ');';
            $byMethod->stmts = (new ParserFactory())->create(ParserFactory::PREFER_PHP5)->parse('<?php ' . $body);
            $classBuilder->addStmt($byMethod);
        }

        $builder->addStmts(array_values($classUses));
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

    public function getAnnotation()
    {
        return CollectionType::class;
    }

    protected function getParentClass(ReflectionClass $reflectedClass)
    {
        $reader = new AnnotationReader();

        $reflectedJsonCollection = new ReflectionClass(JsonCollection::class);
        $parentClassShortName = $reflectedJsonCollection->getShortName();
        $parentClassName = $reflectedJsonCollection->getName();

        $annotation = $reader->getClassAnnotation($reflectedClass, CollectionType::class);
        $interfaceParents = $reflectedClass->getInterfaceNames();
        if ($annotation instanceof CollectionType && $annotation->type) {
            $uses = $this->getUses($reflectedClass);
            $parentClassShortName = $parentClassName = $annotation->type;
            if (isset($uses[$parentClassShortName])) {
                $parentClassName = $uses[$parentClassShortName]['name'];
            }
        } elseif ($reflectedClass->isInterface() && count($interfaceParents) > 0) {
            $parentClass = new ReflectionClass(current($interfaceParents));
            $annotation = $reader->getClassAnnotation($parentClass, CollectionType::class);
            if ($annotation instanceof CollectionType) {
                $parentNamespace = $parentClass->getNamespaceName();
                $parentClassShortName = $parentClass->getShortName() . self::MODEL_SUFFIX;
                $parentClassName = $parentNamespace . '\\' . $parentClassShortName;
            }
        }

        return [$parentClassShortName, $parentClassName];
    }

    protected function getUses(ReflectionClass $class)
    {
        $code = file_get_contents($class->getFileName());
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP5);
        $stmts = $parser->parse($code);
        $useVisitor = new UseVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver()); // we will need resolved names
        $traverser->addVisitor($useVisitor);
        $traverser->traverse($stmts);
        return $useVisitor->getUses();
    }
}

