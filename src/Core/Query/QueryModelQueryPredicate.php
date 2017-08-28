<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

abstract class QueryModelQueryPredicate extends QueryPredicateBase
{
    private $queryModel;

    public function __construct(QueryModel $queryModel)
    {
        $this->queryModel = $queryModel;
    }

    public function __toString()
    {
        return $this->buildQuery($this->queryModel, $this->render());
    }

    /**
     * @return String
     */
    abstract protected function render();
}
