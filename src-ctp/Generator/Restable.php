<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Generator;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Restable
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $get;

    /**
     * @var array
     */
    public $options;
}
