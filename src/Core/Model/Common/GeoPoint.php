<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Model\Common;

/**
 * @package Commercetools\Core\Model\Common
 *
 * @method string getType()
 * @method GeoPoint setType(string $type = null)
 * @method array getCoordinates()
 * @method GeoPoint setCoordinates(array $coordinates = null)
 */
class GeoPoint extends GeoLocation
{
    const TYPE_NAME = 'Point';
    const LONGITUDE = 0;
    const LATITUDE = 1;

    public function getLatitude()
    {
        return $this->getCoordinate(self::LATITUDE);
    }

    public function getLongitude()
    {
        return $this->getCoordinate(self::LONGITUDE);
    }

    private function getCoordinate($index)
    {
        $coordinates = $this->getCoordinates();
        return isset($coordinates[$index]) ? $coordinates[$index] : 0;
    }
}
