<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Generator\JsonResource;
use Ctp\Generator\JsonField;
use Ctp\Generator\Collectable;
use Ctp\Model\Common\LocalizedString;

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
