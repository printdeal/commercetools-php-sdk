<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

abstract class QueryPredicateBase implements QueryPredicate
{
    public function orWhere(QueryPredicate $other)
    {
        return new QueryPredicateConnector("or", $this, $other);
    }

    public function andWhere(QueryPredicate $other)
    {
        return new QueryPredicateConnector("and", $this, $other);
    }

    public function negate()
    {
        return new NegatedQueryPredicate($this);
    }

    protected function buildQuery(QueryModel $queryModel, $definition)
    {
        $current = $queryModel->getPathSegment() . $definition;

        if ($queryModel->getParent() != null) {
            $parent = $queryModel->getParent();
            return $this->buildQuery($parent, $parent->getPathSegment() != null ? "(" . $current . ")" : $current);
        }
        return $current;
    }
}
