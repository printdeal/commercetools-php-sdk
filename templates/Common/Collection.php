<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\JsonField;

class Collection extends JsonObject implements \Iterator, \Countable
{
    const TYPE = '';

    /**
     * @var array
     */
    private $keys = array();

    /**
     * @var int
     */
    private $pos = 0;

    private $indexes = [];

    public function __construct(array $data)
    {
        $this->keys = array_keys($data);
        $this->index($data);
        parent::__construct($data);
    }


    protected function index($data)
    {
    }

    protected function addToIndex($index, $key, $value)
    {
        $this->indexes[$index][$key] = $value;
    }

    protected function valueByKey($index, $key)
    {
        return isset($this->indexes[$index][$key]) ? $this->at($this->indexes[$index][$key]) : null;
    }


    /**
     * @inheritDoc
     */
    public function current()
    {
        if (isset($this->keys[$this->pos])) {
            return $this->at($this->keys[$this->pos]);
        }
        return null;
    }
    
    public function at($index)
    {
        return $this->raw($index);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->pos++;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        if ($this->valid()) {
            return $this->keys[$this->pos];
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return isset($this->keys[$this->pos]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->pos = 0;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->keys);
    }
}
