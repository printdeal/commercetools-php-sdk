<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Customer;

use Ctp\Generator\JsonField;
use Ctp\Generator\JsonResource;
use Ctp\Generator\Collectable;
use Ctp\Generator\Referenceable;
use Ctp\Generator\Queryable;
use Ctp\Generator\Deletable;
use Ctp\Generator\Updatable;
use Ctp\Model\Common\LocalizedString;
use Ctp\Model\Resource;

/**
 * @JsonResource()
 * @Collectable(indexes={"id"})
 * @Referenceable()
 * @Queryable(uri="customers")
 * @Deletable(uri="customers")
 * @Updatable(uri="customers")
 */
interface Customer extends Resource
{
    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getName();
}
