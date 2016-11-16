<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Helper\Generate\FieldType;

class LocalizedEnumType extends BaseType
{
    const FIELD_TYPE = LocalizedEnumValue::class;
    
    /**
     * @FieldType(type="LocalizedEnumValueCollection")
     * @var  LocalizedEnumValueCollection
     */
    private $values;

    public function __construct(array $data)
    {
        $data['name'] = 'LocalizedEnum';
        parent::__construct($data);
    }
}
