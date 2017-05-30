<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

abstract class BaseResourceIdentifier extends JsonObject
{
    const RESOURCE_TYPE_ID = '';

    public function __construct(array $data)
    {
        if (!empty(static::RESOURCE_TYPE_ID)) {
            $data['typeId'] = static::RESOURCE_TYPE_ID;
        }
        parent::__construct($data);
    }
}
