<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Type;

use Commercetools\Model\LocalizedString;
use Commercetools\Generator\JsonField;
use Commercetools\Generator\JsonResource;
use Commercetools\Generator\Collectable;

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
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getLable();
}
