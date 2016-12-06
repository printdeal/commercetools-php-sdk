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
 * @Target({"CLASS"})
 */
class CollectionSetter
{
    /**
     * @Enum({"map", "list"})
     */
    public $type;

    /**
     * @var array
     */
    public $elementTypes = [];
}
