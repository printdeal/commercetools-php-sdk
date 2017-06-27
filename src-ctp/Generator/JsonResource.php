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
class JsonResource
{
    /**
     * @var string
     */
    public $type;
}
