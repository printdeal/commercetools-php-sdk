<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Helper\Generate\FieldType;

class EnumValue extends JsonObject
{
    /**   
     * @FieldType(type="string")
     * @var string
     */
    private $key;

    /**            
     * @FieldType(type="string")
     * @var string
     */
    private $label;
}
