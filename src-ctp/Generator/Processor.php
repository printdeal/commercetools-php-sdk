<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Generator;

interface Processor
{
    public function process(\ReflectionClass $class, $annotation);

    public function getAnnotation();
}
