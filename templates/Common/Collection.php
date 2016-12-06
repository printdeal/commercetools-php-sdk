<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\ArraySerializable;
use Commercetools\Core\Helper\Generate\JsonField;

class Collection implements \Iterator, \Countable, ArraySerializable, \JsonSerializable
{
    private $rawData;

    /**
     * @var array
     */
    private $keys = array();

    /**
     * @var int
     */
    private $pos = 0;

    private $indexes = [];

    protected $data = [];

    public function __construct(array $data =[])
    {
        $this->keys = array_keys($data);
        $this->index($data);
        $this->rawData = $data;
    }

    protected function raw($field)
    {
        if (isset($this->rawData[$field])) {
            return $this->rawData[$field];
        }
        return null;
    }

    protected function rawSet($field, $data)
    {
        if (!is_null($field)) {
            $this->rawData[$field] = $data;
        } else {
            $this->rawData[] = $data;
        }
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritdoc
     */
    public static function fromArray(array $data)
    {
        return new static($data);
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->rawData;
    }

    public function __set($name, $value)
    {
        throw new \BadMethodCallException('Setting values is not allowed');
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
