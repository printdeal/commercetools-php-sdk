<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */


namespace Ctp\Model;

use Ctp\Generator\JsonResource;
use Ctp\Generator\JsonField;
use Ctp\Model\BaseResourceIdentifier;

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
