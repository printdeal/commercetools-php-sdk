<?php 
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */
namespace Commercetools\Core\Templates\Common;

use Commercetools\Core\Helper\Generate\JsonField;
use Commercetools\Core\Helper\Generate\DraftableCollection;

/**
 * Class LocalizedString
 * @package Commercetools\Core\Templates\Common
 * @DraftableCollection(type="map")
 */
class LocalizedString extends Collection
{
    public function __get($locale)
    {
        return $this->at($locale);
    }
}
