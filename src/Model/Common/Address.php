<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model\Common;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\JsonField;
use Commercetools\Generator\Collectable;

/**
 * @JsonResource()
 * @Collectable()
 */
interface Address
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getId();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getTitle();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getSalutation();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getFirstName();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getLastName();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getStreetName();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getStreetNumber();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getAdditionalStreetInfo();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getPostalCode();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getCity();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getRegion();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getState();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getCountry();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getCompany();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getDepartment();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getBuilding();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getApartment();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getPOBox();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getPhone();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getMobile();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getEmail();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getFax();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getAdditionalAddressInfo();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getExternalId();
}
