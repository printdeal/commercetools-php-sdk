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
class Updatable extends Restable
{
    /**
     * @var string
     */
    public $method = 'POST';

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
