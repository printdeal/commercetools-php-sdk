<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Model\Common\LocalizedString;
use Ctp\Generator\JsonField;
use Ctp\Generator\JsonResource;
use Ctp\Generator\Collectable;

/**
 * @JsonResource()
 * @Collectable(indexes={"key"})
 */
interface LocalizedEnumValue
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getKey();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getLabel();
}
