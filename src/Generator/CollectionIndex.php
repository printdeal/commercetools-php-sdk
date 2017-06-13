<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class CollectionIndex
{
    /**
     * @var string
     */
    public $field;
}
