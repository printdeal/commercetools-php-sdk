<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Generator;


interface Processor
{
    public function process(\ReflectionClass $class, $annotation);

    public function getAnnotation();
}
