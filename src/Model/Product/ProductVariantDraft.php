<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 * @created: 04.02.15, 16:41
 */

namespace Sphere\Core\Model\Product;

use Sphere\Core\Model\Common\AttributeCollection;
use Sphere\Core\Model\Common\JsonObject;
use Sphere\Core\Model\Common\PriceCollection;

/**
 * @package Sphere\Core\Model\Product
 * @apidoc http://dev.sphere.io/http-api-projects-products.html#new-product-variant
 * @method string getSku()
 * @method ProductVariantDraft setSku(string $sku = null)
 * @method ProductVariantDraft setPrices(PriceCollection $prices = null)
 * @method ProductVariantDraft setAttributes(AttributeCollection $attributes = null)
 * @method PriceCollection getPrices()
 * @method AttributeCollection getAttributes()
 */
class ProductVariantDraft extends JsonObject
{
    public function getFields()
    {
        return [
            'sku' => [self::TYPE => 'string'],
            'prices' => [self::TYPE => '\Sphere\Core\Model\Common\PriceCollection'],
            'attributes' => [self::TYPE => '\Sphere\Core\Model\Common\AttributeCollection'],
        ];
    }
}