<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model;

interface Collection extends \IteratorAggregate
{
    public function at($index);

    /**
     * @return MapIterator
     */
    public function getIterator();
}
