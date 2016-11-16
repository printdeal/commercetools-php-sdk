<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\FieldType;

class Money extends JsonObject
{
    /**  
     * @FieldType(type="string")
     * @var string
     */
    private $currencyCode;

    /**            
     * @FieldType(type="int")
     * @var int
     */
    private $centAmount;
}
