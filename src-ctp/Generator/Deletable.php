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
class Deletable extends Restable
{
    /**
     * @var string
     */
    public $method = 'DELETE';

    /**
     * @var array
     */
    public $get = [
        QueryType::BY_ID
    ];

    /**
     * @var array
     */
    public $options = [
        QueryOptionType::EXPAND
    ];
}
