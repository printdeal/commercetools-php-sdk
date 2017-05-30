<?php

namespace Commercetools\Model;

use Commercetools\Generator\JsonResource;
use Commercetools\Generator\JsonField;
use Commercetools\Model\JsonObject;
use DateTimeImmutable;

/**
 * @JsonResource()
 */
interface Resource
{
    /**
     * @JsonField(type="string")
     * @return string
     */
    public function getId();

    /**
     * @JsonField(type="int")
     * @return int
     */
    public function getVersion();

    /**
     * @JsonField(type="DateTimeImmutable")
     * @return DateTimeImmutable
     */
    public function getCreatedAt();

    /**
     * @JsonField(type="DateTimeImmutable")
     * @return DateTimeImmutable
     */
    public function getLastModifiedAt();
}
