<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Common\LocalizedString;
use Commercetools\Core\Helper\Generate\FieldType;

class LocalizedEnumValue extends JsonObject
{
    /**  
     * @FieldType(type="string")
     * @var string
     */
    private $key;

    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $label;
}
