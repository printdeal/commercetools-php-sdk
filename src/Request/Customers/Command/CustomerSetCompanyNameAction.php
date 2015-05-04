<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Sphere\Core\Request\Customers\Command;

use Sphere\Core\Request\AbstractAction;

/**
 * Class CustomerSetCompanyNameAction
 * @package Sphere\Core\Request\Customers\Command
 * @link http://dev.sphere.io/http-api-projects-customers.html#set-company-name
 * @method string getCompanyName()
 * @method CustomerSetCompanyNameAction setCompanyName(string $companyName = null)
 * @method string getAction()
 * @method CustomerSetCompanyNameAction setAction(string $action = null)
 */
class CustomerSetCompanyNameAction extends AbstractAction
{
    public function getFields()
    {
        return [
            'action' => [static::TYPE => 'string'],
            'companyName' => [static::TYPE => 'string'],
        ];
    }

    public function __construct()
    {
        $this->setAction('setCompanyName');
    }
}
