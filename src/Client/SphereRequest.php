<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Client;

use Commercetools\Model\JsonObject;
use GuzzleHttp\Psr7\Request;

class SphereRequest extends Request
{
    const RESULT_TYPE = JsonObject::class;
}
