<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Category;

use Commercetools\Core\Helper\Generate\CollectionType;
use Commercetools\Core\Helper\Generate\DraftableCollection;
use Commercetools\Core\Templates\Category;
use Commercetools\Core\Templates\Common\Collection;

/**
 * @CollectionType(type="Category", indexes={"id"})
 * @DraftableCollection(type="list")
 */
class CategoryCollection extends Collection
{

}
