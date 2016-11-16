<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\Reference;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Templates\Type;

class TypeReference extends Reference
{
    /**
     * @JsonField(type="Type")
     * @var Type
     */
    private $obj;
}
