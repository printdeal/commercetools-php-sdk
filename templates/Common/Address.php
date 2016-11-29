<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Helper\Generate\Draftable;

/**
 * Class Address
 * @Draftable(fields={
 *     "title", "salutation", "firstName", "lastName", "streetName", "streetNumber", "additionalStreetInfo",
 *     "postalCode", "city", "region", "state", "country", "company", "department", "building", "apartment",
 *     "pOBox", "phone", "mobile", "email", "fax", "additionalAddressInfo", "externalId"
 * })
 */
class Address extends JsonObject
{
    /**
     * @JsonField(type="string")
     * @var string
     */
    private $id;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $title;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $salutation;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $firstName;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $lastName;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $streetName;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $streetNumber;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $additionalStreetInfo;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $postalCode;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $city;
    /**
     * @JsonField(type="string")
     * @var int
     */
    private $region;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $state;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $country;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $company;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $department;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $building;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $apartment;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $pOBox;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $phone;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $mobile;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $email;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $fax;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $additionalAddressInfo;

    /**
     * @JsonField(type="string")
     * @var int
     */
    private $externalId;
}
