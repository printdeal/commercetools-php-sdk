<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\CustomField;

use Commercetools\Core\Templates\Common\Collection;
use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Templates\Type;
use Commercetools\Core\Templates\Type\FieldDefinitionCollection;
use Commercetools\Core\Templates\Type\FieldDefinition;
use Commercetools\Core\Templates\Type\FieldType;
use Commercetools\Core\Templates\Type\TypeReference;

class FieldContainer extends Collection
{
    private $type;

    public function __construct(array $data, TypeReference $type = null)
    {
        $this->type = $type;

        parent::__construct($data);
    }

    public function at($index)
    {
        $data = $this->raw($index);
        if (!$this->type instanceof TypeReference) {
            return $data;
        }
        $type = $this->type->getObj();
        if ($type instanceof Type) {
            $fieldDefinitions = $type->getFieldDefinitions();

            if ($fieldDefinitions instanceof FieldDefinitionCollection) {
                $fieldDefinition = $fieldDefinitions->byName($index);

                if ($fieldDefinition instanceof FieldDefinition) {
                    $definitionType = $fieldDefinition->getType();

                    if ($definitionType instanceof FieldType) {
                        if ($type = $definitionType->fieldType()) {
                            return new $type($data);
                        }
                    }
                }
            }
        }
        return $data;
    }
}
