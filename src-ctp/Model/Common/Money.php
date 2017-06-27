<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model\Common;

use Ctp\Generator\JsonResource;
use Ctp\Generator\Collectable;
use Ctp\Generator\JsonField;

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
