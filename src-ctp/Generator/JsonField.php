<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Ctp\Generator;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Api
 * @package Ctp\Core\Helper\Generate
 * @Annotation
 */
class JsonField
{
    /**
     * @var string
     */
    public $type;
    public $discriminator;
    public $element;
    /**
     * @var array
     */
    public $params;
}
