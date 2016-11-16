<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Common\LocalizedString;
use Commercetools\Core\Helper\Generate\JsonField;

class FieldDefinition extends JsonObject
{
    /**
     * @JsonField(type="FieldType")
     * @var FieldType
     */
    private $type;

    /**  
     * @JsonField(type="string")
     * @var string
     */
    private $name;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $label;

    /** 
     * @JsonField(type="bool")
     * @var bool
     */
    private $required;

    /**            
     * @JsonField(type="string")
     * @var string
     */
    private $inputHint;
}
