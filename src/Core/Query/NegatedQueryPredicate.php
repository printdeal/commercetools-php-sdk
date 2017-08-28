<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class NegatedQueryPredicate extends QueryPredicateBase
{
    /**
     * @var QueryPredicate
     */
    private $queryPredicate;

    /**
     * @param QueryPredicate $queryPredicate
     */
    public function __construct(QueryPredicate $queryPredicate)
    {
        $this->queryPredicate = $queryPredicate;
    }

    public function __toString()
    {
        return sprintf("not(%s)", (string)$this->queryPredicate);
    }
}
