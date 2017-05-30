<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Generator;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Discriminator
{
    public $name;
    public $callback;
}
