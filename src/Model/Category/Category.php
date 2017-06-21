<?php

namespace Commercetools\Model\Category;

use Commercetools\Core\Model\Category\CategoryReferenceCollection;
use Commercetools\Core\Model\Common\AssetCollection;
use Commercetools\Model\Common\LocalizedString;
use Commercetools\Model\Reference;
use Commercetools\Model\Resource;
use Commercetools\Generator\JsonField;
use Commercetools\Generator\Collectable;
use Commercetools\Generator\JsonResource;
use Commercetools\Generator\Referenceable;
use Commercetools\Model\Type\CustomFieldObject;

/**
 * @JsonResource()
 * @Collectable(indexes={"id"})
 * @Referenceable()
 */
interface Category extends Resource
{
    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getName();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getSlug();

    /**
     * @JsonField(type="LocalizedString")
     */
    public function getDescription();

    /**
     * @JsonField(type="CategoryReferenceCollection")
     * @return CategoryReferenceCollection
     */
    public function getAncestors();

    /**
     * @JsonField(type="CategoryReference")
     * @return CategoryReference
     */
    public function getParent();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getOrderHint();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getExternalId();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getMetaDescription();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getMetaTitle();

    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getMetaKeywords();

    /**
     * @JsonField(type="CustomFieldObject")
     * @return CustomFieldObject
     */
    public function getCustom();

    /**
     * @JsonField(type="AssetCollection")
     * @return AssetCollection
     */
    public function getAssets();
}
