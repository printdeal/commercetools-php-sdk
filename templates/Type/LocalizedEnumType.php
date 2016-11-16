<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Helper\Generate\JsonField;

class LocalizedEnumType extends FieldType
{
    const FIELD_TYPE = LocalizedEnumValue::class;
    
    /**
     * @JsonField(type="LocalizedEnumValueCollection")
     * @var  LocalizedEnumValueCollection
     */
    private $values;

    public function __construct(array $data)
    {
        $data['name'] = 'LocalizedEnum';
        parent::__construct($data);
    }
}
