<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model;

use Ctp\Generator\JsonResource;
use Ctp\Generator\JsonField;
use Ctp\Generator\Discriminator;
use Ctp\Generator\Collectable;

/**
 * @JsonResource()
 * @Collectable()
 */
interface Action
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getAction();
}
