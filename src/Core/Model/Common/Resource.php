<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Model\Common;

use Commercetools\Core\Model\Identifiable;
use DateTime;

/**
 * @package Commercetools\Core\Model\Common
 */
abstract class Resource extends JsonObject implements ReferenceObjectInterface, Identifiable
{
    public function fieldDefinitions()
    {
        return [
            'id' => [static::TYPE => 'string'],
            'version' => [static::TYPE => 'int'],
            'createdAt' => [
                static::TYPE => DateTime::class,
                static::DECORATOR => DateTimeDecorator::class
            ],
            'lastModifiedAt' => [
                static::TYPE => DateTime::class,
                static::DECORATOR => DateTimeDecorator::class
            ],
        ];
    }

    /**
     * @return string
     */
    public function getId()
    {
        /**
         * @var string $id
         */
        $id = parent::getId();
        return $id;
    }

    public function getReferenceIdentifier()
    {
        return $this->getId();
    }

    /**
     * @return Reference
     */
    public function getReference()
    {
        $className = trim(get_called_class(), '\\');
        $referenceClass = $className . 'Reference';
        if (class_exists($referenceClass)) {
            $reference = call_user_func_array(
                $referenceClass . '::ofId',
                [$this->getReferenceIdentifier(), $this->getContextCallback()]
            );
        } else {
            $classParts = explode('\\', $className);
            $class = lcfirst(array_pop($classParts));
            $type = strtolower(preg_replace('/([A-Z])/', '-$1', $class));

            $reference = Reference::ofTypeAndId($type, $this->getReferenceIdentifier(), $this->getContextCallback());
        }
        $reference->setObj($this);

        return $reference;
    }
}
