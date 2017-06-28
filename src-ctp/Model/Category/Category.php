<?php

namespace Ctp\Model\Category;

use Ctp\Model\Common\LocalizedString;
use Ctp\Model\Reference;
use Ctp\Model\Resource;
use Ctp\Generator\JsonField;
use Ctp\Generator\Collectable;
use Ctp\Generator\JsonResource;
use Ctp\Generator\Referenceable;
use Ctp\Generator\Queryable;
use Ctp\Generator\Deletable;
use Ctp\Generator\Updatable;
use Ctp\Generator\QueryType;
use Ctp\Generator\QueryOptionType;
use Ctp\Model\Type\CustomFieldObject;

/**
 * @JsonResource()
 * @Collectable(indexes={"id"})
 * @Referenceable()
 * @Queryable(uri="categories", get={QueryType::QUERY, QueryType::BY_ID, QueryType::BY_KEY})
 * @Deletable(uri="categories", get={QueryType::BY_ID, QueryType::BY_KEY})
 * @Updatable(uri="categories", get={QueryType::BY_ID, QueryType::BY_KEY})
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
     * @return LocalizedString
     */
    public function getDescription();

    /**
     * @JsonField(type="CategoryReferenceCollection")
     * @return CategoryReferenceCollection
     */
    public function getAncestors();

    /**
     * @JsonField(type="Reference")
     * @return Reference
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
