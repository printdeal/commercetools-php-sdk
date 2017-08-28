<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class StringQuerySortingModel extends QueryModel
{
    /**
     * @inheritDoc
     */
    public function is($s)
    {
        return $this->isPredicate($s);
    }

    /**
     * @inheritDoc
     */
    public function isNot($s)
    {
        return $this->isNotPredicate($s);
    }

    /**
     * @inheritDoc
     */
    public function isIn(array $args)
    {
        return new IsInQueryPredicate($this, $this->normalizeValues($args));
    }

    /**
     * @inheritDoc
     */
    public function isGreaterThan($s)
    {
        return ComparisonQueryPredicate::ofIsGreaterThan($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isLessThan($s)
    {
        return ComparisonQueryPredicate::ofIsLessThan($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isLessThanOrEqualTo($s)
    {
        return ComparisonQueryPredicate::ofIsLessThanOrEqualTo($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isGreaterThanOrEqualTo($s)
    {
        return ComparisonQueryPredicate::ofIsGreaterThanOrEqualTo($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isNotIn(array $args)
    {
        return new IsNotInQueryPredicate($this, $this->normalizeValues($args));
    }

    /**
     * @inheritDoc
     */
    public function isPresent()
    {
        return $this->isPresentPredicate();
    }

    /**
     * @inheritDoc
     */
    public function isNotPresent()
    {
        return $this->isNotPresentPredicate();
    }
}
