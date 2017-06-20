<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model\Type;

use Commercetools\Generator\JsonField;
use Commercetools\Generator\JsonResource;

/**
 * @JsonResource()
 */
interface CustomFieldObject
{
    /**
     * @JsonField(type="TypeReference")
     * @return TypeReference
     */
    public function getType();

    /**
     * @JsonField(type="FieldDefinitionCollection", params={"type"})
     * @return FieldDefinitionCollection
     */
    public function getFields();
}
