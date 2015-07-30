<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Sphere\Core\Model\Common;


/**
 * @package Sphere\Core\Model\Common
 * @apidoc http://dev.sphere.io/http-api-projects-products.html#product-variant-attribute
 * @method string getLabel()
 * @method Enum setLabel(string $label = null)
 * @method string getKey()
 * @method Enum setKey(string $key = null)
 */
class Enum extends JsonObject
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'label' => [static::TYPE => 'string'],
            'key' => [static::TYPE => 'string']
        ];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }
}