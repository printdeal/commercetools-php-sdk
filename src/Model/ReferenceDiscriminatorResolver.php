<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

use Commercetools\Data\Model\CategoryReferenceModel;
use Commercetools\Data\Model\ReferenceModel;

class ReferenceDiscriminatorResolver
{
    const TYPES = [
        'category' => CategoryReferenceModel::class
    ];

    public static function discriminatorType($data, $discriminatorName)
    {
        $types = static::TYPES;
        $discriminator = isset($data[$discriminatorName]) ? $data[$discriminatorName] : '';
        return isset($types[$discriminator]) ? $types[$discriminator] : ReferenceModel::class;
    }
}
