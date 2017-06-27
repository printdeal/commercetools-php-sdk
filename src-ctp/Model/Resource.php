<?php

namespace Ctp\Model;

use Ctp\Generator\JsonResource;
use Ctp\Generator\JsonField;
use Ctp\Model\JsonObject;
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
