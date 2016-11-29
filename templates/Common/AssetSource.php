<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\Draftable;
use Commercetools\Core\Helper\Generate\JsonField;

/**
 * Class AssetSource
 * @package Commercetools\Core\Templates\Common
 * @Draftable(fields={"uri", "key", "dimensions", "contentType"})
 */
class AssetSource extends JsonObject
{
    /**
     * @JsonField(type="string")
     * @var string
     */
    private $uri;

    /**
     * @JsonField(type="string")
     * @var string
     */
    private $key;

    /**
     * @JsonField(type="AssetDimension")
     * @var AssetDimension
     */
    private $dimensions;

    /**
     * @JsonField(type="string")
     * @var string
     */
    private $contentType;
}
