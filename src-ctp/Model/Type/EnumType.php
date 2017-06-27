<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Generator\JsonField;
use Ctp\Generator\JsonResource;
use Ctp\Generator\DiscriminatorValue;

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
