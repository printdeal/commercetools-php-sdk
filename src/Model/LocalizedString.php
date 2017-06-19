<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

class LocalizedString extends JsonCollection
{
    public function __get($locale)
    {
        return $this->at($locale);
    }
}
