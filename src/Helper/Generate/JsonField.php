<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Api
 * @package Commercetools\Core\Helper\Generate
 * @Annotation
 * @Target({"PROPERTY"})
 */
class JsonField
{
    /**
     * @var string
     */
    public $type;

    public $discriminator;

    public $element;

    /**
     * @var array
     */
    public $params;
}
