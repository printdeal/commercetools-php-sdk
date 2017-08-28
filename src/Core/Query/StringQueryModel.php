<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

interface StringQueryModel
{
    /**
     * @param string $s
     * @return QueryPredicate
     */
    public function is($s);

    /**
     * @param string $s
     * @return QueryPredicate
     */
    public function isNot($s);

    /**
     * @param string[] $s
     * @return QueryPredicate
     */
    public function isIn(array $s);

    /**
     * @param string $s
     * @return QueryPredicate
     */
    public function isGreaterThan($s);

    /**
     * @param string $s
     * @return QueryPredicate
     */
    public function isLessThan($s);

    /**
     * @param string $s
     * @return QueryPredicate
     */
    public function isLessThanOrEqualTo($s);

    /**
     * @param string $s
     * @return QueryPredicate
     */
    public function isGreaterThanOrEqualTo($s);

    /**
     * @param string[] $s
     * @return QueryPredicate
     */
    public function isNotIn(array $s);

    /**
     * @return QueryPredicate
     */
    public function isPresent();

    /**
     * @return QueryPredicate
     */
    public function isNotPresent();
}
