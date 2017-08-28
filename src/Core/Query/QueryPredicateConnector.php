<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

class QueryPredicateConnector
{
    private $connectorWord;
    private $leftPredicate;
    private $rightPredicate;

    /**
     * QueryPredicateConnector constructor.
     * @param string $connectorWord
     * @param QueryPredicate $leftPredicate
     * @param QueryPredicate $rightPredicate
     *
     */
    public function __construct($connectorWord, QueryPredicate $leftPredicate, QueryPredicate $rightPredicate)
    {
        $this->connectorWord = $connectorWord;
        $this->leftPredicate = $leftPredicate;
        $this->rightPredicate = $rightPredicate;
    }

    public function __toString()
    {
        return sprintf("%s %s %s", (string)$this->leftPredicate, $this->connectorWord, (string)$this->rightPredicate);
    }
}
