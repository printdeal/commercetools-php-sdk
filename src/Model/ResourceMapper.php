<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

use Commercetools\Data\ResourceClassMap;

class ResourceMapper
{
    /**
     * @var ClassMap
     */
    private $classMap;

    public function __construct(ClassMap $classMap = null)
    {
        if (is_null($classMap)) {
            $classMap = new ResourceClassMap();
        }

        $this->classMap = $classMap;
    }

    public function mapToResourceClass($class, $data)
    {
        $class = $this->classMap->getMappedClass($class);
        return new $class($data);
    }
}
