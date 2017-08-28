<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class EmptyCollectionPredicate extends QueryModelQueryPredicate
{
    private $isEmpty;

    public function __construct(QueryModel $queryModel, $isEmpty)
    {
        parent::__construct($queryModel);
        $this->isEmpty = $isEmpty;
    }

    /**
     * @inheritDoc
     */
    protected function render()
    {
        return $this->isEmpty ? " is empty" : " is not empty";
    }
}
