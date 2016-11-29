<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\JsonField;

class ResourceIdentifier extends JsonObject
{
    const RESOURCE_TYPE_ID = '';

    /**     
     * @JsonField(type="string")
     * @var string
     */
    private $typeId;

    /**  
     * @JsonField(type="string")
     * @var string
     */
    protected $id;

    /**  
     * @JsonField(type="string")
     * @var string
     */
    protected $key;

    public function __construct(array $data)
    {
        if (!empty(static::RESOURCE_TYPE_ID)) {
            $data['typeId'] = static::RESOURCE_TYPE_ID;
        }
        parent::__construct($data);
    }
}
