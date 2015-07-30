<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 * @created: 10.02.15, 10:29
 */

namespace Commercetools\Core\Request;


use GuzzleHttp\Message\Response;
use Commercetools\Core\AccessorTrait;
use Commercetools\Core\Client\HttpMethod;

/**
 * Class AbstractCreateRequestTest
 * @package Commercetools\Core\Request
 * @method AbstractQueryRequest getRequest($class, array $args = [])
 */
class AbstractQueryRequestTest extends \PHPUnit_Framework_TestCase
{
    use AccessorTrait;

    const ABSTRACT_QUERY_REQUEST = '\Commercetools\Core\Request\AbstractQueryRequest';

    /**
     * @return AbstractQueryRequest
     */
    protected function getQueryRequest()
    {
        $request = $this->getRequest(static::ABSTRACT_QUERY_REQUEST);

        return $request;
    }

    public function testHttpRequestMethod()
    {
        $request = $this->getQueryRequest();
        $httpRequest = $request->httpRequest();

        $this->assertSame(HttpMethod::GET, $httpRequest->getMethod());
    }

    public function testHttpRequestPath()
    {
        $request = $this->getQueryRequest();
        $httpRequest = $request->httpRequest();

        $this->assertSame('test', (string)$httpRequest->getUri());
    }

    public function testHttpRequestObject()
    {
        $request = $this->getQueryRequest();
        $httpRequest = $request->httpRequest();

        $this->assertEmpty((string)$httpRequest->getBody());
    }

    public function testWhere()
    {
        $request = $this->getQueryRequest();
        $request->where('test');
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?where=test', (string)$httpRequest->getUri());
    }

    public function testExpand()
    {
        $request = $this->getQueryRequest();
        $request->expand('test');
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?expand=test', (string)$httpRequest->getUri());
    }

    public function testSort()
    {
        $request = $this->getQueryRequest();
        $request->sort('test');
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?sort=test', (string)$httpRequest->getUri());
    }

    public function testLimit()
    {
        $request = $this->getQueryRequest();
        $request->limit(1);
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?limit=1', (string)$httpRequest->getUri());
    }

    public function testWithTotalTrue()
    {
        $request = $this->getQueryRequest();
        $request->withTotal(true);
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?withTotal=true', (string)$httpRequest->getUri());
    }

    public function testWithTotalFalse()
    {
        $request = $this->getQueryRequest();
        $request->withTotal(false);
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?withTotal=false', (string)$httpRequest->getUri());
    }

    public function testOffset()
    {
        $request = $this->getQueryRequest();
        $request->offset(1);
        $httpRequest = $request->httpRequest();

        $this->assertSame('test?offset=1', (string)$httpRequest->getUri());
    }

    public function testBuildResponse()
    {
        $guzzleResponse = $this->getMock('\GuzzleHttp\Psr7\Response', [], [], '', false);
        $request = $this->getRequest(static::ABSTRACT_QUERY_REQUEST);
        $response = $request->buildResponse($guzzleResponse);

        $this->assertInstanceOf('\Commercetools\Core\Response\PagedQueryResponse', $response);
    }

    public function testMapResult()
    {
        $request = $this->getRequest(static::ABSTRACT_QUERY_REQUEST);
        $result = $request->mapResult(
            [
                'results' => [
                    ['key' => 'value'],
                    ['key' => 'value'],
                    ['key' => 'value'],
                ]
            ]
        );
        $this->assertInstanceOf('\Commercetools\Core\Model\Common\Collection', $result);
        $this->assertSame(3, count($result));
    }

    public function testMapEmptyResult()
    {
        $request = $this->getRequest(static::ABSTRACT_QUERY_REQUEST);
        $result = $request->mapResult([]);
        $this->assertInstanceOf('\Commercetools\Core\Model\Common\Collection', $result);
    }
}
