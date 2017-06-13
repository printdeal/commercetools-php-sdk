<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

interface Collection extends \IteratorAggregate
{
    public function at($index);
}
