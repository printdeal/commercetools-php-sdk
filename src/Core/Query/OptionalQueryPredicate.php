<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class OptionalQueryPredicate extends QueryModelQueryPredicate
{
    private $isPresent;

    public function __construct(QueryModel $queryModel, $isPresent)
    {
        parent::__construct($queryModel);
        $this->isPresent = $isPresent;
    }

    /**
     * @inheritDoc
     */
    protected function render()
    {
        return $this->isPresent ? " is defined" : " is not defined";
    }
}
