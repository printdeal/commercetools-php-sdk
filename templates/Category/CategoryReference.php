<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Category;

use Commercetools\Core\Templates\Category;
use Commercetools\Core\Templates\Common\Reference;
use Commercetools\Core\Helper\Generate\FieldType;

/**
 * Class CategoryReference
 * @package Commercetools\Core\Templates\Category
 */
class CategoryReference extends Reference
{
    /**
     * @FieldType(type="Category")
     * @var Category
     */
    private $obj;
}
