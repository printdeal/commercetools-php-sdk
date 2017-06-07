<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model;


abstract class ClassMap
{
    protected $types = [];

    public function getMappedClass($class) {
        if (isset($this->types[$class])) {
            return $this->types[$class];
        }
        return $class;
    }
}
