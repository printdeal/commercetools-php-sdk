<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\JsonField;

class Money extends JsonObject
{
    /**  
     * @JsonField(type="string")
     * @var string
     */
    private $currencyCode;

    /**            
     * @JsonField(type="int")
     * @var int
     */
    private $centAmount;
}
