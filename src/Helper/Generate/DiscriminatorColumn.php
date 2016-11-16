<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Helper\Generate;

/**
 * @package Commercetools\Core\Helper\Generate
 * @Annotation
 * @Target({"CLASS"})
 */
class DiscriminatorColumn
{
    public $name;
    public $callback;
}
