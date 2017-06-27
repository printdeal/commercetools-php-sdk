<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model\Type;

use Ctp\Generator\JsonField;
use Ctp\Generator\JsonResource;

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
     * @JsonField(type="FieldContainer", params={"type"})
     * @return FieldContainer
     */
    public function getFields();
}
