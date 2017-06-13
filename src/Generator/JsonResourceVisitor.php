<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use PhpParser\Node;
use PhpParser\Node\Stmt\Interface_;

class JsonResourceVisitor extends GeneratorVisitor
{
    private $resourceClasses = [];
    private $discriminatorClasses = [];
    private $discriminatorValueClasses = [];
    private $collectionClasses = [];

    public function enterNode(Node $node)
    {
        if (!$node instanceof Interface_) {
            return null;
        }
        $reflectedClass = new \ReflectionClass((string)$node->namespacedName);
        $annotation = $this->reader->getClassAnnotation($reflectedClass, JsonResource::class);
        if ($annotation instanceof JsonResource) {
            $this->resourceClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->resourceClasses[$reflectedClass->getName()][JsonResource::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, Discriminator::class);
        if ($annotation instanceof Discriminator) {
            $this->discriminatorClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->discriminatorClasses[$reflectedClass->getName()][Discriminator::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, DiscriminatorValue::class);
        if ($annotation instanceof DiscriminatorValue) {
            $parentInterface = current($reflectedClass->getInterfaceNames());
            $this->discriminatorValueClasses[$parentInterface][$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->discriminatorValueClasses[$parentInterface][$reflectedClass->getName()][DiscriminatorValue::class] = $annotation;
        }
        $annotation = $this->reader->getClassAnnotation($reflectedClass, CollectionType::class);
        if ($annotation instanceof CollectionType) {
            $this->collectionClasses[$reflectedClass->getName()][\ReflectionClass::class] = $reflectedClass;
            $this->collectionClasses[$reflectedClass->getName()][CollectionType::class] = $annotation;
        }

        return $node;
    }

    /**
     * @return array
     */
    public function getResourceClasses()
    {
        return $this->resourceClasses;
    }

    public function getDiscriminatorClasses()
    {
        return $this->discriminatorClasses;
    }

    public function getDiscriminatorValueClasses()
    {
        return $this->discriminatorValueClasses;
    }

    /**
     * @return array
     */
    public function getCollectionClasses()
    {
        return $this->collectionClasses;
    }
}
