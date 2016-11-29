<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Templates\CustomFieldObject;
use Commercetools\Core\Helper\Generate\Draftable;
use Commercetools\Core\Helper\Generate\JsonField;

/**
 * @Draftable(fields={"sources", "name", "description", "tags", "custom"})
 */
class Asset extends JsonObject
{
    /**
     * @JsonField(type="string")
     * @var string
     */
    private $id;

    /**
     * @JsonField(type="string")
     * @var string
     */
    private $sources;

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
    private $tags;

    /**
     * @JsonField(type="CustomFieldObject")
     * @var CustomFieldObject
     */
    private $custom;
}
