<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Common\LocalizedString;
use Commercetools\Core\Helper\Generate\FieldType;

class FieldDefinition extends JsonObject
{
    /**
     * @FieldType(type="BaseType")
     * @var BaseType
     */
    private $type;

    /**  
     * @FieldType(type="string")
     * @var string
     */
    private $name;

    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $label;

    /** 
     * @FieldType(type="bool")
     * @var bool
     */
    private $required;

    /**            
     * @FieldType(type="string")
     * @var string
     */
    private $inputHint;
}
