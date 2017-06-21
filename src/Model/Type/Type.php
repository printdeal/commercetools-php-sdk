<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Type;

use Commercetools\Model\Common\LocalizedString;
use Commercetools\Model\Resource;
use Commercetools\Generator\JsonResource;
use Commercetools\Generator\Collectable;
use Commercetools\Generator\JsonField;
use Commercetools\Generator\Referenceable;

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
