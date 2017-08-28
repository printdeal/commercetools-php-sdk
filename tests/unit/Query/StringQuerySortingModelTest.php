<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;


use PHPUnit\Framework\TestCase;

class StringQuerySortingModelTest extends TestCase
{
    /**
     * @var StringQuerySortingModel
     */
    private $model;

    /**
     * @inheritDoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->model = new StringQuerySortingModel(null, 'id');
    }

    public function testGenerateSimpleQueries()
    {
        $this->assertSame('id="foo"', (string)$this->model->is('foo'));
    }

    public function testStringEscape()
    {
        $this->assertSame('id="foo\"bar"', (string)$this->model->is('foo"bar'));
    }

    public function testIsGreaterThan()
    {
        $this->assertSame('id>"x"', (string)$this->model->isGreaterThan('x'));
    }

    public function testIsGreaterThanOrEqualTo()
    {
        $this->assertSame('id>="x"', (string)$this->model->isGreaterThanOrEqualTo('x'));
    }

    public function testIsLessThan()
    {
        $this->assertSame('id<"x"', (string)$this->model->isLessThan('x'));
    }

    public function testIsLessThanOrEqualTo()
    {
        $this->assertSame('id<="x"', (string)$this->model->isLessThanOrEqualTo('x'));
    }

    public function testIsIn()
    {
        $this->assertSame('id in ("x", "y", "z")', (string)$this->model->isIn(['x', 'y', 'z']));
    }

    public function testIsNotIn()
    {
        $this->assertSame('id not in ("x", "y", "z")', (string)$this->model->isNotIn(['x', 'y', 'z']));
    }

    public function testIsNotPresent()
    {
        $this->assertSame('id is not defined', (string)$this->model->isNotPresent());
    }

    public function testIsPresent()
    {
        $this->assertSame('id is defined', (string)$this->model->isPresent());
    }

    public function generateHiearchicalQueries()
    {
        $parent = new QueryModel(
            new QueryModel(
                new QueryModel(
                    new QueryModel(null, 'x1'),
                    'x2'),
                'x3'),
            'x4');
        $this->assertSame(
            'x1(x2(x3(x4(x5="foo"))))',
            (new StringQuerySortingModel($parent, 'x5'))->is('foo')
        );
    }
}
