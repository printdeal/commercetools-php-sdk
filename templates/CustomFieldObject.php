<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\CustomField\FieldContainer;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Helper\Generate\Draftable;
use Commercetools\Core\Templates\Type\TypeReference;

/**
 * Class CustomFieldObject
 * @package Commercetools\Core\Templates
 * @Draftable(fields={"type", "fields"})
 */
class CustomFieldObject extends JsonObject
{
    /**
     * @JsonField(type="TypeReference")
     * @var TypeReference
     */
    private $type;
    /**
     * @JsonField(type="FieldContainer", params={"type"})
     * @var FieldContainer
     */
    private $fields;
}
