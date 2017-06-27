<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Generator\JsonResource;
use Ctp\Generator\Discriminator;
use Ctp\Generator\JsonField;

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
