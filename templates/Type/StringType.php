<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Core\Templates\Type;

class StringType extends BaseType
{
    public function __construct(array $data)
    {
        $data['name'] = 'String';
        parent::__construct($data);
    }
}
