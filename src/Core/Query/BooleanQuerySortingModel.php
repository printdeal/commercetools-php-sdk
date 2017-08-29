<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class BooleanQuerySortingModel extends QueryModel
{
    /**
     * @inheritDoc
     */
    public function is($value)
    {
        $value = (bool)$value ? "true" : "false";
        return $this->isPredicate($value);
    }
}
