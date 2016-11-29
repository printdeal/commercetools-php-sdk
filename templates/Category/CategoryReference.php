<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Category;

use Commercetools\Core\Templates\Category;
use Commercetools\Core\Templates\Common\Reference;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Helper\Generate\Draftable;
use Commercetools\Core\Helper\Generate\ReferenceType;

/**
 * Class CategoryReference
 * @package Commercetools\Core\Templates\Category
 * @Draftable(fields={"id", "key"})
 * @ReferenceType(type="category")
 */
class CategoryReference extends Reference
{
    /**
     * @JsonField(type="Category")
     * @var Category
     */
    private $obj;
}
