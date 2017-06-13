<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Category;

use Commercetools\Model\Collection;
use Commercetools\Generator\CollectionType;

/**
 * @CollectionType()
 */
interface CategoryCollection extends Collection
{
    /**
     * @JsonField(type="Category")
     * @return Category
     */
    public function at($index);

    /**
     * @CollectionIndex(field="id")
     * @return Category
     */
    public function byId($id);
}
