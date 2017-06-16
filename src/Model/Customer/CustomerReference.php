<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Customer;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\DiscriminatorValue;
use Commercetools\Model\Reference;

/**
 * @JsonResource()
 * @DiscriminatorValue(value="customer")
 */
interface CustomerReference extends Reference
{

}
