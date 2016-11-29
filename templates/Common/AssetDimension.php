<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\Draftable;
use Commercetools\Core\Helper\Generate\JsonField;

class AssetDimension extends JsonObject
{
    /**
     * @JsonField(type="int")
     * @var int
     */
    private $w;

    /**
     * @JsonField(type="int")
     * @var int
     */
    private $h;
}
