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
class ReferenceType
{
    /**
     * @var string
     */
    public $type;
}
