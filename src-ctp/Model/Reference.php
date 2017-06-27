<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model;

use Ctp\Generator\JsonResource;
use Ctp\Generator\JsonField;
use Ctp\Model\JsonObject;
use Ctp\Generator\Discriminator;

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
