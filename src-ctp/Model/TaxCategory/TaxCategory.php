<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\TaxCategory;

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
interface TaxCategory extends Resource
{
    /**
     * @JsonField(type="LocalizedString")
     * @return LocalizedString
     */
    public function getName();
}
