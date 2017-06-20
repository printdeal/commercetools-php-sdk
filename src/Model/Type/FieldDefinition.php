<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Type;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\JsonField;
use Commercetools\Generator\Collectable;
use Commercetools\Model\LocalizedString;

/**
 * @JsonResource()
 * @Collectable(indexes={"name"})
 */
interface FieldDefinition
{
    /**
     * @JsonField(type="FieldType")
     * @return FieldType
     */
    public function getType();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getName();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getLabel();

    /**
     * @JsonField(type="bool")
     * @return bool
     */
    public function getRequired();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getInputHint();
}
