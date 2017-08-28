<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;


use PHPUnit\Framework\TestCase;

class QueryPredicateTest extends TestCase
{
    /**
     * @var QueryPredicate
     */
    private $p1;

    /**
     * @var QueryPredicate
     */
    private $p2;

    /**
     * @inheritDoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->p1 = new SimpleQueryPredicate('masterData(current(slug(en="xyz-42")');
        $this->p2 = new SimpleQueryPredicate('tags contains all ("a", "b", "c")');
    }

    public function testCorrectClass()
    {
        $this->assertInstanceOf(QueryPredicateBase::class, $this->p1);
        $this->assertInstanceOf(QueryPredicateBase::class, $this->p2);
    }

    public function testOr()
    {
        $this->assertSame(
            'masterData(current(slug(en="xyz-42") or tags contains all ("a", "b", "c")',
            (string)$this->p1->orWhere($this->p2)
        );
    }

    public function testAnd()
    {
        $this->assertSame(
            'masterData(current(slug(en="xyz-42") and tags contains all ("a", "b", "c")',
            (string)$this->p1->andWhere($this->p2)
        );
    }

    public function testNegated()
    {
        $this->assertSame('not(masterData(current(slug(en="xyz-42"))', (string)$this->p1->negate());
    }

    public function testToString()
    {
        $this->assertSame('masterData(current(slug(en="xyz-42")', (string)$this->p1);
    }
}
