<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Helper\Generate\JsonField;

class EnumType extends FieldType
{
    const FIELD_TYPE = EnumValue::class;
    
    /**
     * @JsonField(type="EnumValueCollection")
     * @var EnumValueCollection
     */
    private $values;

    public function __construct(array $data)
    {
        $data['name'] = 'Enum';
        parent::__construct($data);
    }
}
