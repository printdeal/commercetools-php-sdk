<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Type;

use Commercetools\Generator\JsonField;
use Commercetools\Generator\JsonResource;
use Commercetools\Generator\DiscriminatorValue;

/**
 * @JsonResource()
 * @DiscriminatorValue(value="Enum")
 */
interface EnumType extends FieldType
{
    /**
     * @JsonField(type="EnumValueCollection")
     * @return EnumValueCollection
     */
    public function getValues();
}
