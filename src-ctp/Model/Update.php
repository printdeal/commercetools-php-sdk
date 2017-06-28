<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model;

use Ctp\Generator\JsonResource;
use Ctp\Generator\JsonField;

/**
 * @JsonResource()
 */
interface Update
{
    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getVersion();

    /**
     * @JsonField(type="ActionCollection")
     * @return ActionCollection
     */
    public function getActions();
}
