<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Generator\JsonField;
use Ctp\Generator\JsonResource;
use Ctp\Generator\Collectable;

/**
 * @JsonResource()
 * @Collectable(indexes={"key"})
 */
interface EnumValue
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getKey();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getLabel();
}
