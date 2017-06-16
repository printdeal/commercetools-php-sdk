<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Customer;


use Commercetools\Generator\JsonField;
use Commercetools\Generator\JsonResource;
use Commercetools\Generator\CollectionType;
use Commercetools\Core\Model\Common\LocalizedString;
use Commercetools\Model\Resource;

/**
 * @JsonResource()
 * @CollectionType(indexes={"id"})
 */
interface Customer extends Resource
{
    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getName();
}
