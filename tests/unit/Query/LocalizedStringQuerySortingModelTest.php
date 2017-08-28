<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

use PHPUnit\Framework\TestCase;

class LocalizedStringQuerySortingModelTest extends TestCase
{
    private $model;

    /**
     * @inheritDoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->model = new LocalizedStringQuerySortingModel(null, 'thepath');
    }


    public function testLang()
    {
        $this->assertSame('thepath(en="bar")', (string)$this->model->lang('en')->is('bar'));
    }

    public function testIsIn()
    {
        $this->assertSame('thepath(en in ("foo", "bar"))', (string)$this->model->lang('en')->isIn(['foo', 'bar']));
    }

    public function testIsNot()
    {
        $this->assertSame('thepath(en<>"bar")',  (string)$this->model->lang('en')->isNot( 'bar'));
    }
}
