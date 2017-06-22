<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Commercetools\Model;

use Commercetools\Client\SphereRequest;
use Psr\Http\Message\ResponseInterface;

class ResultMapper
{
    public function map(SphereRequest $request, ResponseInterface $response)
    {
        return $this->mapResponseToClass($request::RESULT_TYPE, $response);
    }

    public function mapResponseToClass($class, ResponseInterface $response)
    {
        $body = (string)$response->getBody();
        $json = json_decode($body, true);
        return $this->mapResultToClass($class, $json);
    }

    public function mapResultToClass($class, $data)
    {
        $type = ResourceClassMap::getMappedClass($class);
        return new $type($data);
    }
}
