<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Model\Cart;

use Commercetools\Core\Model\Channel\ChannelReference;
use Commercetools\Core\Model\Common\JsonObject;
use Commercetools\Core\Model\Common\LocalizedString;
use Commercetools\Core\Model\Common\Money;
use Commercetools\Core\Model\Common\Price;
use Commercetools\Core\Model\Order\ItemState;
use Commercetools\Core\Model\Order\ItemStateCollection;
use Commercetools\Core\Model\Product\ProductVariant;
use Commercetools\Core\Model\TaxCategory\TaxRate;

/**
 * @package Commercetools\Core\Model\Cart
 * @apidoc http://dev.sphere.io/http-api-projects-carts.html#line-item
 * @method string getId()
 * @method LineItem setId(string $id = null)
 * @method string getProductId()
 * @method LineItem setProductId(string $productId = null)
 * @method LocalizedString getName()
 * @method LineItem setName(LocalizedString $name = null)
 * @method ProductVariant getVariant()
 * @method LineItem setVariant(ProductVariant $variant = null)
 * @method Price getPrice()
 * @method LineItem setPrice(Price $price = null)
 * @method int getQuantity()
 * @method LineItem setQuantity(int $quantity = null)
 * @method ItemStateCollection getState()
 * @method LineItem setState(ItemStateCollection $state = null)
 * @method TaxRate getTaxRate()
 * @method LineItem setTaxRate(TaxRate $taxRate = null)
 * @method ChannelReference getSupplyChannel()
 * @method LineItem setSupplyChannel(ChannelReference $supplyChannel = null)
 * @method DiscountedLineItemPrice getDiscountedPrice()
 * @method LineItem setDiscountedPrice(DiscountedLineItemPrice $discountedPrice = null)
 * @method LocalizedString getProductSlug()
 * @method LineItem setProductSlug(LocalizedString $productSlug = null)
 * @method ChannelReference getDistributionChannel()
 * @method LineItem setDistributionChannel(ChannelReference $distributionChannel = null)
 */
class LineItem extends JsonObject
{
    public function getFields()
    {
        return [
            'id' => [static::TYPE => 'string'],
            'productId' => [static::TYPE => 'string'],
            'name' => [static::TYPE => '\Commercetools\Core\Model\Common\LocalizedString'],
            'productSlug' => [static::TYPE => '\Commercetools\Core\Model\Common\LocalizedString'],
            'variant' => [static::TYPE => '\Commercetools\Core\Model\Product\ProductVariant'],
            'price' => [static::TYPE => '\Commercetools\Core\Model\Common\Price'],
            'quantity' => [static::TYPE => 'int'],
            'state' => [static::TYPE => '\Commercetools\Core\Model\Order\ItemStateCollection'],
            'taxRate' => [static::TYPE => '\Commercetools\Core\Model\TaxCategory\TaxRate'],
            'supplyChannel' => [static::TYPE => '\Commercetools\Core\Model\Channel\ChannelReference'],
            'discountedPrice' => [static::TYPE => '\Commercetools\Core\Model\Cart\DiscountedLineItemPrice'],
            'distributionChannel' => [static::TYPE => '\Commercetools\Core\Model\Channel\ChannelReference'],
        ];
    }

    /**
     * @return Money
     */
    public function getTotal()
    {
        $price = $this->getPrice()->getValue();
        $amount = $this->getQuantity() * $price->getCentAmount();
        return Money::ofCurrencyAndAmount($price->getCurrencyCode(), $amount, $this->getContext());
    }
}
