<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Helper\Generate;

interface ArraySerializable
{
    /**
     * @return array
     */
    public function toArray();
    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data);
}
