<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

class ResourceClassMap
{
    /**
     * @var ClassMap
     */
    private static $classMap;

    private static function getClassMap()
    {
        if (is_null(self::$classMap)) {
            self::$classMap = new ResourceModelClassMap();
        }
        return self::$classMap;
    }

    public static function setClassMap(ClassMap $classMap)
    {
        self::$classMap = $classMap;
    }

    public static function getMappedClass($class)
    {
        return self::getClassMap()->getMappedClass($class);
    }
}
