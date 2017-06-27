<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Model;

use Ctp\Client\SphereRequest;
use Psr\Http\Message\ResponseInterface;

class ResultMapper
{
    public static function map(SphereRequest $request, ResponseInterface $response)
    {
        return static::mapResponseToClass($request::RESULT_TYPE, $response);
    }

    public static function mapResponseToClass($class, ResponseInterface $response)
    {
        $body = (string)$response->getBody();
        $json = json_decode($body, true);
        return static::mapResultToClass($class, $json);
    }

    public static function mapResultToClass($class, $data)
    {
        $type = ResourceClassMap::getMappedClass($class);
        return new $type($data);
    }
}
