<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class LocalizedStringQuerySortingModelImpl extends QueryModelImpl implements LocalizedStringQuerySortingModel
{
    /**
     * @inheritdoc
     */
    public function lang($locale)
    {
        return $this->locale($locale);
    }

    /**
     * @inheritdoc
     */
    public function locale($locale)
    {
        return $this->stringModel($locale);
    }

    /**
     * @inheritdoc
     */
    public function isNotPresent()
    {
        return $this->isNotPresentPredicate();
    }

    /**
     * @inheritdoc
     */
    public function isPresent()
    {
        return $this->isPresentPredicate();
    }
}
