<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Helper\Generate\JsonField;

class EnumValue extends JsonObject
{
    /**   
     * @JsonField(type="string")
     * @var string
     */
    private $key;

    /**            
     * @JsonField(type="string")
     * @var string
     */
    private $label;
}
