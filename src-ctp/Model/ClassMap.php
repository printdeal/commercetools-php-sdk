<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model;

abstract class ClassMap
{
    protected static $types = [];

    public function getMappedClass($class)
    {
        if (isset(static::$types[$class])) {
            return static::$types[$class];
        }
        return $class;
    }
}
