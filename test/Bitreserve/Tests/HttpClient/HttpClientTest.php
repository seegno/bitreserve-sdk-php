<?php

namespace Bitreserve\Tests\HttpClient;

use Bitreserve\BitreserveClient;
use Bitreserve\HttpClient\Handler\ErrorHandler;
use Bitreserve\HttpClient\HttpClient;
use Bitreserve\HttpClient\Listener\ErrorListener;
use Bitreserve\HttpClient\Message\ResponseMediator;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use ReflectionProperty;

class HttpClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnInstanceOfHttpClient()
    {
        $httpClient = new HttpClient();

        $this->assertInstanceOf('Bitreserve\HttpClient\HttpClient', $httpClient);
    }

    /**
     * @test
     */
    public function shouldBeAbleToPassOptionsToConstructor()
    {
        $options = array(
            'timeout' => 33,
            'foo' => 'bar',
        );

        $httpClient = new HttpClient($options);

        $this->assertEquals(33, $httpClient->getOption('timeout'));
        $this->assertEquals('bar', $httpClient->getOption('foo'));
    }

    /**
     * @test
     */
    public function shouldBeAbleToSetOption()
    {
        $httpClient = new HttpClient();
        $httpClient->setOption('timeout', 15);

        $this->assertEquals(15, $httpClient->getOption('timeout'));
    }

    /**
     * @test
     */
    public function shouldDoGETRequest()
    {
        $headers = array('c' => 'd');
        $parameters = array('a' => 'b');
        $path = '/some/path';

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($path, null, 'GET', $headers, array('query' => $parameters));

        $httpClient->get($path, $parameters, $headers);
    }

    /**
     * @test
     */
    public function shouldDoPOSTRequest()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $path = '/some/path';

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($path, $body, 'POST', $headers);

        $httpClient->post($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoPOSTRequestWithoutContent()
    {
        $path = '/some/path';

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($path, null, 'POST', $this->isType('array'));

        $httpClient->post($path);
    }

    /**
     * @test
     */
    public function shouldDoPATCHRequest()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $path = '/some/path';

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($path, $body, 'PATCH', $headers);

        $httpClient->patch($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoDELETERequest()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $path = '/some/path';

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($path, $body, 'DELETE', $headers);

        $httpClient->delete($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoPUTequest()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $path = '/some/path';

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method('request')
            ->with($path, $body, 'PUT', $headers);

        $httpClient->put($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoCustomRequest()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $httpMethod = 'custom';
        $options = array('e' => 'f');
        $path = '/some/path';

        $request = new Request($httpMethod, $path);
        $response = new Response(200, array('foo' => 'bar'));

        $httpClient = $this->getMockBuilder('Bitreserve\HttpClient\HttpClient')
            ->setMethods(array('createRequest'))
            ->getMock();

        $client = $this->getClientMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with($httpMethod, $path, array_merge($options, array('headers' => $headers, 'body' => $body)))
            ->will($this->returnValue($request));

        $client->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->returnValue($response));

        $clientReflector = new ReflectionProperty('Bitreserve\HttpClient\HttpClient', 'client');
        $clientReflector->setAccessible(true);
        $clientReflector->setValue($httpClient, $client);

        $httpClientResponse = $httpClient->request($path, $body, $httpMethod, $headers, $options);

        $this->assertEquals($response, $httpClientResponse);
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\LogicException
     */
    public function shouldThrowLogicExceptionWhenClientSendThrownsLogicException()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $httpMethod = 'custom';
        $options = array('e' => 'f');
        $path = '/some/path';

        $request = new Request($httpMethod, $path);
        $response = new Response(200, array('foo' => 'bar'));

        $httpClient = $this->getMockBuilder('Bitreserve\HttpClient\HttpClient')
            ->setMethods(array('createRequest'))
            ->getMock();

        $client = $this->getClientMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with($httpMethod, $path, array_merge($options, array('headers' => $headers, 'body' => $body)))
            ->will($this->returnValue($request));

        $client->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->throwException(new \LogicException('Testing logic exception', 500)));

        $clientReflector = new ReflectionProperty('Bitreserve\HttpClient\HttpClient', 'client');
        $clientReflector->setAccessible(true);
        $clientReflector->setValue($httpClient, $client);

        $errorHandler = new ErrorHandler();

        $errorHandlerReflector = new ReflectionProperty('Bitreserve\HttpClient\HttpClient', 'errorHandler');
        $errorHandlerReflector->setAccessible(true);
        $errorHandlerReflector->setValue($httpClient, $errorHandler);

        $httpClientResponse = $httpClient->request($path, $body, $httpMethod, $headers, $options);
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\RuntimeException
     */
    public function shouldThownRuntimeExceptionWhenClientSendThrownsRuntimeException()
    {
        $body = array('a' => 'b');
        $headers = array('c' => 'd');
        $httpMethod = 'custom';
        $options = array('e' => 'f');
        $path = '/some/path';

        $request = new Request($httpMethod, $path);
        $response = new Response(200, array('foo' => 'bar'));

        $httpClient = $this->getMockBuilder('Bitreserve\HttpClient\HttpClient')
            ->setMethods(array('createRequest'))
            ->getMock();

        $client = $this->getClientMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with($httpMethod, $path, array_merge($options, array('headers' => $headers, 'body' => $body)))
            ->will($this->returnValue($request));

        $client->expects($this->once())
            ->method('send')
            ->with($request)
            ->will($this->throwException(new \RuntimeException('Testing runtime exception', 500)));

        $clientReflector = new ReflectionProperty('Bitreserve\HttpClient\HttpClient', 'client');
        $clientReflector->setAccessible(true);
        $clientReflector->setValue($httpClient, $client);

        $errorHandler = new ErrorHandler();

        $errorHandlerReflector = new ReflectionProperty('Bitreserve\HttpClient\HttpClient', 'errorHandler');
        $errorHandlerReflector->setAccessible(true);
        $errorHandlerReflector->setValue($httpClient, $errorHandler);

        $httpClientResponse = $httpClient->request($path, $body, $httpMethod, $headers, $options);
    }

    /**
     * @test
     */
    public function shouldAllowToReturnRawContent()
    {
        $headers = array('c' => 'd');
        $httpMethod = 'get';
        $parameters = array('a = b');
        $path = '/some/path';

        $message = $this->getMock('GuzzleHttp\Message\Response', array(), array(200));
        $message->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('Just raw context'));

        $httpClient = new HttpClient();

        $client = $this->getClientMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->will($this->returnValue(new Request($httpMethod, $path)));

        $client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($message));

        $clientReflector = new ReflectionProperty('Bitreserve\HttpClient\HttpClient', 'client');
        $clientReflector->setAccessible(true);
        $clientReflector->setValue($httpClient, $client);

        $response = $httpClient->get($path, $parameters, $headers);

        $this->assertEquals("Just raw context", $response->getBody());
        $this->assertInstanceOf('GuzzleHttp\Message\MessageInterface', $response);
    }

    protected function getHttpClientMock()
    {
        $methods = array('request');

        return $this->getMockBuilder('Bitreserve\HttpClient\HttpClient')
            ->setMethods($methods)
            ->getMock();
    }

    protected function getClientMock()
    {
        return $this->getMockBuilder('GuzzleHttp\Client')
            ->setMethods(array('send', 'createRequest'))
            ->getMock();
    }
}
