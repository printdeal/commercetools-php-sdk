<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

use Commercetools\Core\Model\Common\GeoPoint;

class QueryModel
{
    /**
     * @var QueryModel
     */
    private $parent;

    /**
     * @var string
     */
    private $pathSegment;

    /**
     * QueryModelImpl constructor.
     * @param QueryModel $parent
     * @param string $pathSegment
     */
    public function __construct(QueryModel $parent = null, $pathSegment = null)
    {
        $this->parent = $parent;
        $this->pathSegment = $pathSegment;
    }

    /**
     * @return QueryModel
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getPathSegment()
    {
        return $this->pathSegment;
    }

    protected function stringModel($pathSegment, QueryModel $parent = null)
    {
        if ($parent == null) {
            return $this->stringModel($pathSegment, $this);
        }
        return new StringQuerySortingModel($parent, $pathSegment);
    }

    protected function isNotPredicate($value)
    {
        $normalizedValue = is_string($value) ? $this->normalize($value) : $value;
        return ComparisonQueryPredicate::ofIsNotEqualTo($this, $normalizedValue);
    }

    protected function isPredicate($value)
    {
        $normalizedValue = is_string($value) ? $this->normalize($value) : $value;
        return ComparisonQueryPredicate::ofIsEqualTo($this, $normalizedValue);
    }

    protected function isPresentPredicate()
    {
        return new OptionalQueryPredicate($this, true);
    }

    protected function isNotPresentPredicate()
    {
        return new OptionalQueryPredicate($this, false);
    }

    protected function isEmptyCollectionQueryPredicate()
    {
        return new EmptyCollectionPredicate($this, true);
    }

    protected function isNotEmptyCollectionQueryPredicate()
    {
        return new EmptyCollectionPredicate($this, false);
    }

    protected function withinCirclePredicate(GeoPoint $center, $radius)
    {
        return new WithinCirclePredicate($this, $center, $radius);
    }

    protected function escape($s)
    {
        return str_replace('"', '\"', $s);
    }

    protected function normalizeValues(array $values)
    {
        return array_map([$this, "normalize"], $values);
    }

    protected function normalize($s)
    {
        return '"' . $this->escape($s) . '"';
    }
}
