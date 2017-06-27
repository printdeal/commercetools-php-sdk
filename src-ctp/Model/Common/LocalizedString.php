<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model\Common;

use Ctp\Model\JsonCollection;

class LocalizedString extends JsonCollection
{
    public function __get($locale)
    {
        return $this->at($locale);
    }
}
