<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Sphere\Core\Request\Orders\Command;

use Sphere\Core\Model\Common\Context;
use Sphere\Core\Request\AbstractAction;

/**
 * @package Sphere\Core\Request\Orders\Command
 * @apidoc http://dev.sphere.io/http-api-projects-orders.html#change-payment-state
 * @method string getAction()
 * @method OrderChangePaymentStateAction setAction(string $action = null)
 * @method string getPaymentState()
 * @method OrderChangePaymentStateAction setPaymentState(string $paymentState = null)
 */
class OrderChangePaymentStateAction extends AbstractAction
{
    /**
     * @param array $data
     * @param Context|callable $context
     */
    public function __construct(array $data = [], $context = null)
    {
        parent::__construct($data, $context);
        $this->setAction('changePaymentState');
    }

    public function getFields()
    {
        return [
            'action' => [static::TYPE => 'string'],
            'paymentState' => [static::TYPE => 'string']
        ];
    }

    /**
     * @param string $paymentState
     * @param Context|callable $context
     * @return OrderChangePaymentStateAction
     */
    public static function ofPaymentState($paymentState, $context = null)
    {
        return static::of($context)->setPaymentState($paymentState);
    }
}