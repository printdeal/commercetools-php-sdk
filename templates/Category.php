<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates;

use DateTimeImmutable;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Category\CategoryReferenceCollection;
use Commercetools\Core\Templates\Common\LocalizedString;
/**
 * Class Category
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
     * @JsonField(type="CustomFieldObject")
     * @var CustomFieldObject
     */
    private $custom;
}
