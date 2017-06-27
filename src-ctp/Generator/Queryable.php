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
class Queryable
{
    /**
     * @var string
     */
    public $uri;

    /**
     * @var string
     */
    public $method = 'GET';

    /**
     * @var array
     */
    public $get;
}
