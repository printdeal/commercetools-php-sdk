<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class ComparisonQueryPredicate extends QueryModelQueryPredicate
{
    private $value;
    private $sign;

    public function __construct(QueryModel $queryModel, $value, $sign)
    {
        parent::__construct($queryModel);
        $this->value = $value;
        $this->sign = $sign;
    }

    public static function ofIsEqualTo($queryModel, $value)
    {
        return static::of($queryModel, $value, "=");
    }

    public static function ofIsNotEqualTo($queryModel, $value)
    {
        return static::of($queryModel, $value, "<>");
    }

    public static function ofIsLessThanOrEqualTo($queryModel, $value)
    {
        return static::of($queryModel, $value, "<=");
    }

    public static function ofIsLessThan($queryModel, $value)
    {
        return static::of($queryModel, $value, "<");
    }

    public static function ofIsGreaterThan($queryModel, $value)
    {
        return static::of($queryModel, $value, ">");
    }

    public static function ofIsGreaterThanOrEqualTo($queryModel, $value)
    {
        return static::of($queryModel, $value, ">=");
    }

    private static function of($queryModel, $value, $sign)
    {
        return new ComparisonQueryPredicate($queryModel, $value, $sign);
    }

    public function render()
    {
        return $this->sign . (string)$this->value;
    }
}
