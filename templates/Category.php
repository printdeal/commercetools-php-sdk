<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates;

use Commercetools\Core\Templates\Category\CategoryReference;
use DateTimeImmutable;
use Commercetools\Core\Helper\Generate\Draftable;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Category\CategoryReferenceCollection;
use Commercetools\Core\Templates\Common\LocalizedString;
/**
 * @Draftable(fields={
 *     "name", "description", "parent", "slug", "orderHint", "externalId", "metaTitle", "metaDescription", "metaKeywords", "custom"
 * })
 */
class Category extends JsonObject
{
    /**    
     * @JsonField(type="int")
     * @var int
     */
    private $id;
    
    /**
     * @JsonField(type="int")
     * @var int
     */
    private $version;
    /**
     * @JsonField(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $createdAt;
    /**
     * @JsonField(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $lastModifiedAt;
    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $name;
    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $slug;
    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $description;
    /**
     * @JsonField(type="CategoryReferenceCollection")
     * @var CategoryReferenceCollection
     */
    private $ancestors;

    /**
     * @JsonField(type="CategoryReference")
     * @var CategoryReference
     */
    private $parent;

    /**
     * @JsonField(type="string")
     * @var string
     */
    private $orderHint;

    /**
     * @JsonField(type="string")
     * @var string
     */
    private $externalId;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $metaTitle;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $metaDescription;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $metaKeywords;

    /**
     * @JsonField(type="CustomFieldObject")
     * @var CustomFieldObject
     */
    private $custom;
}
