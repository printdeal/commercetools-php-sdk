<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Common\LocalizedString;
use Commercetools\Core\Helper\Generate\JsonField;

class LocalizedEnumValue extends JsonObject
{
    /**  
     * @JsonField(type="string")
     * @var string
     */
    private $key;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $label;
}
