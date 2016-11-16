<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\CustomField\FieldContainer;
use Commercetools\Core\Helper\Generate\FieldType;
use Commercetools\Core\Templates\Type\TypeReference;

class CustomFieldObject extends JsonObject
{
    /**
     * @FieldType(type="TypeReference")
     * @var TypeReference
     */
    private $type;
    /**
     * @FieldType(type="FieldContainer", params={"type"})
     * @var FieldContainer
     */
    private $fields;
}
