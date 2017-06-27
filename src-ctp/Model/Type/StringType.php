<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model\Type;

use Ctp\Generator\JsonResource;
use Ctp\Generator\DiscriminatorValue;

/**
 * @JsonResource()
 * @DiscriminatorValue(value="String")
 */
interface StringType extends FieldType
{

}
