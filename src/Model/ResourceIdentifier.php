<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Commercetools\Model;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\JsonField;
use Commercetools\Model\BaseResourceIdentifier;

/**
 * @JsonResource(type="BaseResourceIdentifier")
 */
interface ResourceIdentifier
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getTypeId();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getId();

    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getKey();
}
