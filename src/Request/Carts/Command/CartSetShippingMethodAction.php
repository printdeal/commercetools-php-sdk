<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Request\Carts\Command;

use Commercetools\Core\Model\Common\Context;
use Commercetools\Core\Model\ShippingMethod\ShippingMethodReference;
use Commercetools\Core\Request\AbstractAction;

/**
 * @package Commercetools\Core\Request\Carts\Command
 * @apidoc http://dev.sphere.io/http-api-projects-carts.html#set-shipping-method
 * @method string getAction()
 * @method CartSetShippingMethodAction setAction(string $action = null)
 * @method ShippingMethodReference getShippingMethod()
 * @method CartSetShippingMethodAction setShippingMethod(ShippingMethodReference $shippingMethod = null)
 */
class CartSetShippingMethodAction extends AbstractAction
{
    public function getFields()
    {
        return [
            'action' => [static::TYPE => 'string'],
            'shippingMethod' => [static::TYPE => '\Commercetools\Core\Model\ShippingMethod\ShippingMethodReference'],
        ];
    }

    /**
     * @param array $data
     * @param Context|callable $context
     */
    public function __construct(array $data = [], $context = null)
    {
        parent::__construct($data, $context);
        $this->setAction('setShippingMethod');
    }
}
