<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class IsNotInQueryPredicate extends IsInQueryPredicate
{
    /**
     * @param QueryModel $queryModel
     * @param array $values
     */
    public function __construct(QueryModel $queryModel, array $values)
    {
        parent::__construct($queryModel, $values);
    }

    /**
     * @inheritDoc
     */
    protected function render()
    {
        return " not" . parent::render();
    }
}
