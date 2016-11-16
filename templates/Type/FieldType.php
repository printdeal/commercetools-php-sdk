<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Helper\Generate\DiscriminatorColumn;
use Commercetools\Core\Helper\Generate\JsonField;

/**
 * @package Commercetools\Core\Templates\Type
 * @DiscriminatorColumn(name="name", callback="FieldType::discriminatorType")
 */
class FieldType extends JsonObject
{
    const FIELD_TYPE = '';
    const TYPES = [
        'Boolean' => BooleanType::class,
        'String' => StringType::class,
        'Enum' => EnumType::class,
        'LocalizedEnum' => LocalizedEnumType::class
    ];
    /**  
     * @JsonField(type="string")
     * @var string
     */
    private $name;

    public static function discriminatorType($data, $discriminatorName)
    {
        $types = static::TYPES;
        $discriminator = isset($data[$discriminatorName]) ? $data[$discriminatorName] : '';
        return isset($types[$discriminator]) ? $types[$discriminator] : static::class;
    }

    public function fieldType()
    {
        if (!empty(static::FIELD_TYPE)) {
            return static::FIELD_TYPE;
        }
        return null;
    }
}
