<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class CollectionType
{
    /**
     * @var string
     */
    public $elementType;

    /**
     * @var array
     */
    public $indexes = [];
}
