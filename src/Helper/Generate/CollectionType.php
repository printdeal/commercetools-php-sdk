<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

use Doctrine\Common\Annotations\Annotation;

/**
 * @package Commercetools\Core\Helper\Generate
 * @Annotation
 * @Target({"CLASS"})
 */
class CollectionType
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $element;

    /**
     * @var bool
     */
    public $ignore = false;

    /**
     * @var array
     */
    public $indexes = [];
}
