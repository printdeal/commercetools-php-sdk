<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 * @created: 09.02.15, 13:29
 */

namespace Sphere\Core\Model\Common;


/**
 * Class ReferenceFromArrayTrait
 * @package Sphere\Core\Model\Common
 * @method __construct(string $id)
 * @method setRawData(array $data)
 */
trait ReferenceFromArrayTrait
{
    public static function fromArray(array $data)
    {
        $reference = new static($data['id']);
        $reference->setRawData($data);

        return $reference;
    }
}
