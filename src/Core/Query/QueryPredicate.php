<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

interface QueryPredicate
{
    public function orWhere(QueryPredicate $other);

    public function andWhere(QueryPredicate $other);

    public function negate();

    public function __toString();
}
