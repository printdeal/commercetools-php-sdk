<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Model\Cart;

use Commercetools\Core\Model\Common\Context;
use Commercetools\Core\Model\Common\JsonObject;
use Commercetools\Core\Model\CustomField\CustomFieldObjectDraft;
use Commercetools\Core\Model\Common\Address;
use Commercetools\Core\Model\ShippingMethod\ShippingMethodReference;

/**
 * @package Commercetools\Core\Model\Cart
 * @link https://dev.commercetools.com/http-api-projects-carts.html#cart-draft
 * @method string getCurrency()
 * @method string getCustomerId()
 * @method string getCountry()
 * @method string getInventoryMode()
 * @method CartDraft setCurrency(string $currency = null)
 * @method CartDraft setCustomerId(string $customerId = null)
 * @method CartDraft setCountry(string $country = null)
 * @method CartDraft setInventoryMode(string $inventoryMode = null)
 * @method CustomFieldObjectDraft getCustom()
 * @method CartDraft setCustom(CustomFieldObjectDraft $custom = null)
 * @method string getCustomerEmail()
 * @method CartDraft setCustomerEmail(string $customerEmail = null)
 * @method LineItemDraftCollection getLineItems()
 * @method CartDraft setLineItems(LineItemDraftCollection $lineItems = null)
 * @method Address getShippingAddress()
 * @method CartDraft setShippingAddress(Address $shippingAddress = null)
 * @method Address getBillingAddress()
 * @method CartDraft setBillingAddress(Address $billingAddress = null)
 * @method ShippingMethodReference getShippingMethod()
 * @method CartDraft setShippingMethod(ShippingMethodReference $shippingMethod = null)
 * @method CustomLineItemDraftCollection getCustomLineItems()
 * @method CartDraft setCustomLineItems(CustomLineItemDraftCollection $customLineItems = null)
 */
class CartDraft extends JsonObject
{
    public function fieldDefinitions()
    {
        return [
            'currency' => [static::TYPE => 'string'],
            'customerId' => [static::TYPE => 'string'],
            'customerEmail' => [static::TYPE => 'string'],
            'country' => [static::TYPE => 'string'],
            'inventoryMode' => [static::TYPE => 'string'],
            'lineItems' => [static::TYPE => '\Commercetools\Core\Model\Cart\LineItemDraftCollection'],
            'customLineItems' => [static::TYPE => '\Commercetools\Core\Model\Cart\CustomLineItemDraftCollection'],
            'shippingAddress' => [static::TYPE => '\Commercetools\Core\Model\Common\Address'],
            'billingAddress' => [static::TYPE => '\Commercetools\Core\Model\Common\Address'],
            'shippingMethod' => [static::TYPE => '\Commercetools\Core\Model\ShippingMethod\ShippingMethodReference'],
            'custom' => [static::TYPE => '\Commercetools\Core\Model\CustomField\CustomFieldObjectDraft'],
        ];
    }

    /**
     * @param string $currency
     * @param Context|callable $context
     * @return CartDraft
     */
    public static function ofCurrency($currency, $context = null)
    {
        $draft = static::of($context);
        return $draft->setCurrency($currency);
    }
}
