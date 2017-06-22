<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\JsonField;

/**
 * @JsonResource()
 */
interface PagedQueryResult
{
    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getOffset();

    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getCount();

    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getTotal();

    /**
     * @JsonField(type="Collection")
     * @return Collection
     */
    public function getResults();

    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getFacets();
}
