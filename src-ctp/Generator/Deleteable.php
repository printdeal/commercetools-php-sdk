<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Generator;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Deleteable
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $method = 'DELETE';

    /**
     * @var array
     */
    public $get;
}
