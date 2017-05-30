<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\JsonField;
use Commercetools\Model\JsonObject;
use Commercetools\Generator\Discriminator;

/**
 * @JsonResource()
 * @Discriminator(name="typeId")
 */
interface Reference extends ResourceIdentifier
{
    /**
     * @JsonField(type="JsonObject")
     * @return JsonObject
     */
    public function getObj();
}
