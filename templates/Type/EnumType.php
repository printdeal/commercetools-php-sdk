<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Helper\Generate\FieldType;

class EnumType extends BaseType
{
    const FIELD_TYPE = EnumValue::class;
    
    /**
     * @FieldType(type="EnumValueCollection")
     * @var EnumValueCollection
     */
    private $values;

    public function __construct(array $data)
    {
        $data['name'] = 'Enum';
        parent::__construct($data);
    }
}
