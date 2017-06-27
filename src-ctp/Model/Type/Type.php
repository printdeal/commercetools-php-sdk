<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Model\Common\LocalizedString;
use Ctp\Model\Resource;
use Ctp\Generator\JsonResource;
use Ctp\Generator\Collectable;
use Ctp\Generator\JsonField;
use Ctp\Generator\Referenceable;

/**
 * @JsonResource()
 * @Collectable(indexes={"key"})
 * @Referenceable()
 */
interface Type extends Resource
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
    public function getName();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getDescription();

    /**
     * @JsonField(type="array")
     * @return array
     */
    public function getResourceTypeIds();

    /**
     * @JsonField(type="FieldDefinitionCollection")
     * @return FieldDefinitionCollection
     */
    public function getFieldDefinitions();
}
