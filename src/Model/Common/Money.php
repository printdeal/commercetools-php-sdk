<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model\Common;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\Collectable;
use Commercetools\Generator\JsonField;

/**
 * @JsonResource()
 * @Collectable()
 */
interface Money
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getCentAmount();
}
