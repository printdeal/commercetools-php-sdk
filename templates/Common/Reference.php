<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Templates\Category;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Helper\Generate\DiscriminatorColumn;

/**
 * Class Reference
 * @package Commercetools\Core\Templates\Common
 * @DiscriminatorColumn(name="typeId", callback="Reference::discriminatorType")
 */
class Reference extends ResourceIdentifier
{
    const TYPES = [
        'category' => Category::class
    ];

    /**
     * @JsonField(type="JsonObject")
     * @var JsonObject
     */
    private $obj;

    public static function discriminatorType($data, $discriminatorName)
    {
        $types = static::TYPES;
        $discriminator = isset($data[$discriminatorName]) ? $data[$discriminatorName] : '';

        return isset($types[$discriminator]) ? $types[$discriminator] : static::class;
    }
}
