<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\FieldType;

class ResourceIdentifier extends JsonObject
{
    const RESOURCE_TYPE_ID = '';

    /**     
     * @FieldType(type="string")
     * @var string
     */
    private $typeId;

    /**  
     * @FieldType(type="string")
     * @var string
     */
    private $id;

    /**  
     * @FieldType(type="string")
     * @var string
     */
    private $key;

    public function __construct(array $data)
    {
        if (!empty(static::RESOURCE_TYPE_ID)) {
            $data['typeId'] = static::RESOURCE_TYPE_ID;
        }
        parent::__construct($data);
    }
}
