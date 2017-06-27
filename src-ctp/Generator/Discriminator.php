<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Generator;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Discriminator
{
    public $name;
    public $callback;
}
