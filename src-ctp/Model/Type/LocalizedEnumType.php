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
 * @DiscriminatorValue(value="LocalizedEnum")
 */
interface LocalizedEnumType extends FieldType
{
    /**
     * @JsonField(type="LocalizedEnumValueCollection")
     * @return LocalizedEnumValueCollection
     */
    public function getValues();
}
