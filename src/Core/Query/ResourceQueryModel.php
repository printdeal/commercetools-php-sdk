<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

use Commercetools\Core\Model\Identifiable;

class ResourceQueryModel extends QueryModel
{
    public function is(Identifiable $identifier)
    {
        return $this->id()->is($identifier->getId());
    }

    public function id()
    {
        return $this->stringModel("id");
    }

    public function createdAt()
    {
        return new TimestampQuerySortingModel($this, "createdAt");
    }

    public function lastModifiedAt()
    {
        return new TimestampQuerySortingModel($this, "lastModifiedAt");
    }

    public function not(QueryPredicate $queryPredicateToNegate)
    {
        return $queryPredicateToNegate->negate();
    }
}
