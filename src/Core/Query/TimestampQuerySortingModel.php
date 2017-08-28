<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class TimestampQuerySortingModel extends QueryModel
{
    /**
     * @inheritDoc
     */
    public function is(\DateTime $s)
    {
        return $this->isPredicate($s);
    }

    /**
     * @inheritDoc
     */
    public function isNot(\DateTime $s)
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
    public function isGreaterThan(\DateTime $s)
    {
        return ComparisonQueryPredicate::ofIsGreaterThan($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isLessThan(\DateTime $s)
    {
        return ComparisonQueryPredicate::ofIsLessThan($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isLessThanOrEqualTo(\DateTime $s)
    {
        return ComparisonQueryPredicate::ofIsLessThanOrEqualTo($this, $this->normalize($s));
    }

    /**
     * @inheritDoc
     */
    public function isGreaterThanOrEqualTo(\DateTime $s)
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

    protected function normalize($s)
    {
        if ($s instanceof \DateTime || $s instanceof \DateTimeImmutable) {
            if ($s instanceof \DateTime) {
                $time = \DateTimeImmutable::createFromMutable($s);
            } else {
                $time = $s;
            }
            $time = $time->setTimezone(new \DateTimeZone("UTC"))->format('c');
        } else {
            $time = $s;
        }
        return parent::normalize($time);
    }
}
