<?php
/**
 * @author @ct-jensschulze <jens.schulze@commercetools.de>
 */

namespace Sphere\Core\Request\Customers;


use Sphere\Core\Client\HttpMethod;
use Sphere\Core\RequestTestCase;

class CustomerPasswordTokenRequestTest extends RequestTestCase
{
    const CUSTOMER_PASSWORD_REQUEST = '\Sphere\Core\Request\Customers\CustomerPasswordTokenRequest';

    public function testHttpRequestMethod()
    {
        $request = $this->getRequest(static::CUSTOMER_PASSWORD_REQUEST, ['john.doe@company.com']);
        $httpRequest = $request->httpRequest();

        $this->assertSame(HttpMethod::POST, $httpRequest->getHttpMethod());
    }

    public function testHttpRequestPath()
    {
        $request = $this->getRequest(static::CUSTOMER_PASSWORD_REQUEST, ['john.doe@company.com']);
        $httpRequest = $request->httpRequest();

        $this->assertSame('customers/password-token', $httpRequest->getPath());
    }

    public function testHttpRequestObject()
    {
        $request = $this->getRequest(static::CUSTOMER_PASSWORD_REQUEST, ['john.doe@company.com']);
        $httpRequest = $request->httpRequest();

        $this->assertJsonStringEqualsJsonString(
            json_encode(
                ['email' => 'john.doe@company.com']
            ),
            $httpRequest->getBody()
        );
    }

    public function testBuildResponse()
    {
        $guzzleResponse = $this->getMock('\GuzzleHttp\Message\Response', [], [], '', false);
        $request = $this->getRequest(static::CUSTOMER_PASSWORD_REQUEST, ['john.doe@company.com']);
        $response = $request->buildResponse($guzzleResponse);

        $this->assertInstanceOf('\Sphere\Core\Response\SingleResourceResponse', $response);
    }
}