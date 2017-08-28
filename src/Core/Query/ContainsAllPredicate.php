<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class ContainsAllPredicate extends QueryModelQueryPredicate
{
    private $values;

    public function __construct(QueryModel $queryModel, array $values)
    {
        parent::__construct($queryModel);
        $this->values = $values;
    }

    /**
     * @inheritDoc
     */
    protected function render()
    {
        return " contains all (" . implode(", ", $this->values) . ")";
    }
}
