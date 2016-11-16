<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates;

use DateTimeImmutable;
use Commercetools\Core\Helper\Generate\FieldType;
use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Category\CategoryReferenceCollection;
use Commercetools\Core\Templates\Common\LocalizedString;
/**
 * Class Category
 */
class Category extends JsonObject
{
    /**    
     * @FieldType(type="int")
     * @var int
     */
    private $id;
    
    /**
     * @FieldType(type="int")
     * @var int
     */
    private $version;
    /**
     * @FieldType(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $createdAt;
    /**
     * @FieldType(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $lastModifiedAt;
    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $name;
    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $slug;
    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $description;
    /**
     * @FieldType(type="CategoryReferenceCollection")
     * @var CategoryReferenceCollection
     */
    private $ancestors;
    /**
     * @FieldType(type="CustomFieldObject")
     * @var CustomFieldObject
     */
    private $custom;
}
