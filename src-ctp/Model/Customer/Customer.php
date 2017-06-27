<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Customer;

use Ctp\Generator\JsonField;
use Ctp\Generator\JsonResource;
use Ctp\Generator\Collectable;
use Ctp\Generator\Referenceable;
use Ctp\Model\Common\LocalizedString;
use Ctp\Model\Resource;

/**
 * @JsonResource()
 * @Collectable(indexes={"id"})
 * @Referenceable()
 */
interface Customer extends Resource
{
    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getName();
}
