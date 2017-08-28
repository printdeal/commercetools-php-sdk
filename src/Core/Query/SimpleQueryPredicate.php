<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class SimpleQueryPredicate extends QueryPredicateBase
{
    /**
     * @var string
     */
    private $sphereQuery;

    /**
     * SimpleQueryPredicate constructor.
     * @param string $sphereQuery
     */
    public function __construct($sphereQuery)
    {
        $this->sphereQuery = $sphereQuery;
    }

    public function __toString()
    {
        return $this->sphereQuery;
    }
}
