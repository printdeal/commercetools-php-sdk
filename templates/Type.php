<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates;

use Commercetools\Core\Templates\Type\FieldDefinitionCollection;
use DateTimeImmutable;
use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Common\LocalizedString;
use Commercetools\Core\Helper\Generate\JsonField;

class Type extends JsonObject
{
    /**    
     * @JsonField(type="string")
     * @var string
     */
    private $id;

    /**            
     * @JsonField(type="int")
     * @var int
     */
    private $version;

    /**            
     * @JsonField(type="string")
     * @var string
     */
    private $key;

    /**
     * @JsonField(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $createdAt;

    /**
     * @JsonField(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $lastModifiedAt;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $name;

    /**
     * @JsonField(type="LocalizedString")
     * @var LocalizedString
     */
    private $description;

    /**      
     * @JsonField(type="array")
     * @var array
     */
    private $resourceTypeIds;

    /**
     * @JsonField(type="FieldDefinitionCollection")
     * @var FieldDefinitionCollection
     */
    private $fieldDefinitions;
}
