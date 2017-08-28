<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Query;

use Commercetools\Core\Model\Common\GeoPoint;

class WithinCirclePredicate extends QueryModelQueryPredicate
{
    /**
     * @var GeoPoint
     */
    private $center;

    /**
     * @var float
     */
    private $radius;

    public function __construct(QueryModel $queryModel, GeoPoint $center, $radius)
    {
        parent::__construct($queryModel);
        $this->center = $center;
        $this->radius = $radius;
    }

    /**
     * @inheritDoc
     */
    protected function render()
    {
        return sprintf(
            ' within circle(%f, %f, %f)',
            $this->center->getLongitude(),
            $this->center->getLatitude(),
            $this->radius
        );
    }
}
