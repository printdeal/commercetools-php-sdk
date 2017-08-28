<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

interface QueryDsl
{
    public function plusPredicate(QueryPredicate $predicate);
}
