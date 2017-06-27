<?php
/**
 * @author @jayS-de <jens.schulze@commercetools.de>
 */

namespace Ctp\Client;

use Ctp\Model\JsonObject;
use Ctp\Model\ResultMapper;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7;

class SphereRequest extends Request
{
    const RESULT_TYPE = JsonObject::class;

    private $queryParts;
    private $query;

    public function map(ResponseInterface $response)
    {
        return ResultMapper::mapResponseToClass(static::RESULT_TYPE, $response);
    }

    public function withQueryParam($parameterName, $value)
    {
        $query = $this->getUri()->getQuery();
        if ($this->query !== $query) {
            $this->queryParts = Psr7\parse_query($query);
        }
        if (isset($this->queryParts[$parameterName]) && !is_array($this->queryParts[$parameterName])) {
            $this->queryParts[$parameterName] = [$this->queryParts[$parameterName]];
        }
        $this->queryParts[$parameterName][] = $value;
        ksort($this->queryParts);
        $this->query = Psr7\build_query($this->queryParts);

        return $this->withUri($this->getUri()->withQuery($this->query));
    }
}
