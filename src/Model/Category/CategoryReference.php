<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Category;

use Commercetools\Model\Reference;
use Commercetools\Generator\JsonResource;
use Commercetools\Generator\DiscriminatorValue;

/**
 * @JsonResource()
 * @DiscriminatorValue(value="category")
 */
interface CategoryReference extends Reference
{

}
