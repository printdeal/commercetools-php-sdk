<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates;

use Commercetools\Core\Templates\Type\FieldDefinitionCollection;
use DateTimeImmutable;
use Commercetools\Core\Templates\Common\JsonObject;
use Commercetools\Core\Templates\Common\LocalizedString;
use Commercetools\Core\Helper\Generate\FieldType;

class Type extends JsonObject
{
    /**    
     * @FieldType(type="string")
     * @var string
     */
    private $id;

    /**            
     * @FieldType(type="int")
     * @var int
     */
    private $version;

    /**            
     * @FieldType(type="string")
     * @var string
     */
    private $key;

    /**
     * @FieldType(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $createdAt;

    /**
     * @FieldType(type="DateTimeImmutable")
     * @var DateTimeImmutable
     */
    private $lastModifiedAt;

    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $name;

    /**
     * @FieldType(type="LocalizedString")
     * @var LocalizedString
     */
    private $description;

    /**      
     * @FieldType(type="array")
     * @var array
     */
    private $resourceTypeIds;

    /**
     * @FieldType(type="FieldDefinitionCollection")
     * @var FieldDefinitionCollection
     */
    private $fieldDefinitions;
}
