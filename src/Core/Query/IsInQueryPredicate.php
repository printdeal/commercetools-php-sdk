<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class IsInQueryPredicate extends QueryModelQueryPredicate
{
    /**
     * @var array
     */
    private $values;

    /**
     * @param QueryModel $queryModel
     * @param array $values
     */
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
        return " in (" . implode(", ", $this->values). ")";
    }
}
