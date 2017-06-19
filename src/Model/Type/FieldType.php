<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model\Type;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\Discriminator;
use Commercetools\Generator\JsonField;

/**
 * @JsonResource()
 * @Discriminator(name="name")
 */
interface FieldType
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getName();
}
