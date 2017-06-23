<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Client;

use Commercetools\Model\JsonObject;
use Commercetools\Model\ResultMapper;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

class SphereRequest extends Request
{
    const RESULT_TYPE = JsonObject::class;

    public function map(ResponseInterface $response)
    {
        return ResultMapper::mapResponseToClass(static::RESULT_TYPE, $response);
    }
}
