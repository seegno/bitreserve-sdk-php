<?php

namespace Bitreserve\Tests\Unit\Model;

use Bitreserve\Tests\Unit\TestCase as BaseTestCase;

/**
 * TestCase.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Get Model class.
     *
     * @return string
     */
    abstract protected function getModelClass();

    /**
     * Get Model mock.
     *
     * @return mixed
     */
    protected function getModelMock()
    {
        $httpClient = $this->getMock('GuzzleHttp\Client', array('send'));
        $httpClient->expects($this->any())
            ->method('send');

        $httpMock = $this->getMock('Bitreserve\HttpClient\HttpClient', array(), array(array(), $httpClient));

        $client = new \Bitreserve\BitreserveClient(null, $httpMock);
        $client->setHttpClient($httpMock);

        return $this->getMockBuilder($this->getModelClass())
            ->setMethods(array('get', 'post', 'patch', 'delete', 'put', 'head'))
            ->setConstructorArgs(array($client))
            ->getMock();
    }

    /**
     * Get BitreserveClient mock.
     *
     * @param array $methods Mocked methods.
     *
     * @return BitreserveClient
     */
    protected function getBitreserveClientMock(array $methods = array())
    {
        $methods = array_merge(
            array('get', 'post', 'patch', 'put', 'delete', 'request', 'setOption', 'setHeaders', 'getOption'),
            $methods
        );

        return $this->getMock('Bitreserve\BitreserveClient', $methods);
    }

    /**
     * Get HttpClient mock.
     *
     * @param array $methods Mocked methods.
     *
     * @return HttpClient
     */
    protected function getHttpClientMock(array $methods = array())
    {
        $methods = array_merge(
            array('get', 'post', 'patch', 'put', 'delete', 'request', 'setOption', 'setHeaders', 'getOption'),
            $methods
        );

        return $this->getMock('Bitreserve\HttpClient\HttpClientInterface', $methods);
    }

    /**
     * Get Response mock.
     *
     * @param mixed $content Response content.
     *
     * @return Response
     */
    protected function getResponseMock($content = null)
    {
        $response = $this->getMockBuilder('Bitreserve\HttpClient\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();

        if (null === $content) {
            return $response;
        }

        $response->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));

        return $response;
    }
}
