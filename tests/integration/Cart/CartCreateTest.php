<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Cart;

use Commercetools\Core\ApiTestCase;
use Commercetools\Core\Model\Cart\Cart;
use Commercetools\Core\Model\Cart\CartDraft;
use Commercetools\Core\Request\Carts\CartCreateRequest;
use Commercetools\Core\Request\Carts\CartDeleteRequest;

class CartCreateTest extends ApiTestCase
{
    /**
     * @return CartDraft
     */
    protected function getDraft()
    {
        $draft = CartDraft::ofCurrency('EUR')->setCountry('DE');

        return $draft;
    }

    protected function createCart(CartDraft $draft)
    {
        /**
         * @var Cart $cart
         */
        $cartResponse = $this->getClient()
            ->execute(CartCreateRequest::ofDraft($draft));

        $cart = $cartResponse->toObject();

        $this->cleanupRequests[] = CartDeleteRequest::ofIdAndVersion($cart->getId(), $cart->getVersion());

        return $cart;
    }


    public function testCreate()
    {
        $draft = $this->getDraft();
        $cart = $this->createCart($draft);
        $this->assertSame($draft->getCurrency(), $cart->getTotalPrice()->getCurrencyCode());
        $this->assertSame($draft->getCountry(), $cart->getCountry());
        $this->assertSame(0, $cart->getTotalPrice()->getCentAmount());
    }
}
