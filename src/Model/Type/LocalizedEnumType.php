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
