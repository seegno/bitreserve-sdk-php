<?php

namespace Bitreserve\Tests;

use Bitreserve\BitreserveClient;
use Bitreserve\HttpClient;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class BitreserveClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldReturnInstanceOfBitreserveClient()
    {
        $client = new BitreserveClient();

        $this->assertInstanceOf('Bitreserve\BitreserveClient', $client);
    }

    /**
     * @test
     */
    public function shouldReturnInstanceOfHttpClient()
    {
        $client = new BitreserveClient();

        $this->assertInstanceOf('Bitreserve\HttpClient\HttpClient', $client->getHttpClient());
    }

    /**
     * @test
     */
    public function shouldReturnInstanceOfErrorHandler()
    {
        $client = new BitreserveClient();

        $this->assertInstanceOf('Bitreserve\HttpClient\Handler\ErrorHandler', $client->getOption('errorHandler'));
    }

    /**
     * @test
     */
    public function shouldReturnBearerWhenPassingInConstructor()
    {
        $client = new BitreserveClient('bearer');

        $this->assertEquals('bearer', $client->getOption('bearer'));
    }

    /**
     * @test
     */
    public function shouldReturnATokenModel()
    {
        $client = new BitreserveClient('bearer');

        $this->assertInstanceOf('Bitreserve\Model\Token', $client->getToken());
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\AuthenticationRequiredException
     */
    public function shouldThrowAuthenticationRequiredExceptionWhenGettingToken()
    {
        $client = new BitreserveClient();
        $client->getToken();
    }

    /**
     * @test
     */
    public function shouldReturnTickers()
    {
        $data = array(array(
            'ask' => '1',
            'bid' => '1',
            'currency' => 'BTC',
            'pair' => 'BTCBTC',
        ), array(
            'ask' => '440.99',
            'bid' => '440',
            'currency' => 'USD',
            'pair' => 'BTCUSD',
        ));

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('get'))
            ->getMock();

        $client->expects($this->once())
            ->method('get')
            ->with('/ticker')
            ->will($this->returnValue($data));

        $tickers = $client->getTicker();

        $this->assertCount(count($data), $tickers);

        foreach ($tickers as $ticker) {
            $this->assertInstanceOf('Bitreserve\Model\Ticker', $ticker);
        }
    }

    /**
     * @test
     */
    public function shouldReturnTickersByCurrency()
    {
        $expectedCurrency = 'BTC';

        $data = array(array(
            'ask' => '1',
            'bid' => '1',
            'currency' => 'BTC',
            'pair' => 'BTCBTC',
        ), array(
            'ask' => '440.99',
            'bid' => '440',
            'currency' => 'USD',
            'pair' => 'BTCUSD',
        ));

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('get'))
            ->getMock();

        $client->expects($this->once())
            ->method('get')
            ->with(sprintf('/ticker/%s', $expectedCurrency))
            ->will($this->returnValue($data));

        $tickers = $client->getTickerByCurrency($expectedCurrency);

        $this->assertCount(count($data), $tickers);

        foreach ($tickers as $ticker) {
            $this->assertInstanceOf('Bitreserve\Model\Ticker', $ticker);

            $this->assertRegExp(sprintf('/%s/', $expectedCurrency), $ticker->getPair());
        }
    }

    /**
     * @test
     */
    public function shouldReturnCurrencies()
    {
        $data = array(array(
            'ask' => '1',
            'bid' => '1',
            'currency' => 'BTC',
            'pair' => 'BTCBTC',
        ), array(
            'ask' => '440.99',
            'bid' => '440',
            'currency' => 'USD',
            'pair' => 'BTCUSD',
        ));

        $expectedCurrencies = array('BTC', 'USD');

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('get'))
            ->getMock();

        $client->expects($this->once())
            ->method('get')
            ->with('/ticker')
            ->will($this->returnValue($data));

        $currencies = $client->getCurrencies();

        $this->assertCount(count($data), $currencies);

        foreach ($currencies as $currency) {
            $this->assertContains($currency, $expectedCurrencies);
        }
    }

    /**
     * @test
     */
    public function shouldReturnTransactions()
    {
        $data = array(array(
            'id' => 'a97bb994-6e24-4a89-b653-e0a6d0bcf634',
            'foo' => 'bar',
        ), array(
            'id' => '63dc7ccb-0e57-400d-8ea7-7d903753801c',
            'foo' => 'bar',
        ));

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/reserve/transactions')
            ->will($this->returnValue($data));

        $transactions = $client->getTransactions();

        foreach ($transactions as $transaction) {
            $this->assertInstanceOf('Bitreserve\Model\Transaction', $transaction);
        }
    }

    /**
     * @test
     */
    public function shouldReturnOneTransaction()
    {
        $expectedTransactionId = 'a97bb994-6e24-4a89-b653-e0a6d0bcf634';

        $data = array(
            'id' => 'a97bb994-6e24-4a89-b653-e0a6d0bcf634',
            'foo' => 'bar',
        );

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with(sprintf('/reserve/transactions/%s', $expectedTransactionId))
            ->will($this->returnValue($data));

        $transaction = $client->getTransactionById($expectedTransactionId);

        $this->assertInstanceOf('Bitreserve\Model\Transaction', $transaction);
        $this->assertEquals($expectedTransactionId, $transaction->getId());
    }

    /**
     * @test
     */
    public function shouldReturnReserve()
    {
        $client = new BitreserveClient();

        $this->assertInstanceOf('Bitreserve\Model\Reserve', $client->getReserve());
    }

    /**
     * @test
     */
    public function shouldReturnUser()
    {
        $data = array('username' => 'foobar');

        $client = $this->getBitreserveClientMock();

        $client->expects($this->once())
            ->method('getOption')
            ->with('bearer')
            ->will($this->returnValue('token'));

        $client->expects($this->once())
            ->method('get')
            ->will($this->returnValue($data));

        $user = $client->getUser();

        $this->assertInstanceOf('Bitreserve\Model\User', $user);
        $this->assertEquals($data['username'], $user->getUsername());
    }

    /**
     * @test
     */
    public function shouldCreateToken()
    {
        $login = 'foobar';
        $password = 'foobar';
        $description = 'foobar';
        $otp = null;

        $headers = array(
            'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:%s', $login, $password))),
            'X-Bitreserve-OTP' => $otp,
        );

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('getDefaultHeaders', 'post'))
            ->getMock();

        $client->expects($this->any())
            ->method('getDefaultHeaders')
            ->will($this->returnValue(array()));

        $client->expects($this->once())
            ->method('post')
            ->with('/me/tokens', array('description' => $description), $headers)
            ->will($this->returnValue(array('foo' => 'bar')));

        $this->assertEquals(array('foo' => 'bar'), $client->createToken($login, $password, $description));
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithOTP()
    {
        $login = 'foobar';
        $password = 'foobar';
        $description = 'foobar';
        $otp = 'foobar';

        $headers = array(
            'Authorization' => sprintf('Basic %s', base64_encode(sprintf('%s:%s', $login, $password))),
            'X-Bitreserve-OTP' => $otp,
        );

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('getDefaultHeaders', 'post'))
            ->getMock();

        $client->expects($this->any())
            ->method('getDefaultHeaders')
            ->will($this->returnValue(array()));

        $client->expects($this->once())
            ->method('post')
            ->with('/me/tokens', array('description' => $description), $headers)
            ->will($this->returnValue(array('foo' => 'bar')));

        $this->assertEquals(array('foo' => 'bar'), $client->createToken($login, $password, $description, $otp));
    }

    /**
     * @test
     * @dataProvider getDefaultRequestHttpMethods
     */
    public function shouldSendRequestToClient($httpMethod, $encodedBody)
    {
        $apiVersion = 'v0';
        $defaultOptions = array('defaultOption' => 'defaultValue');
        $options = array('option1' => 'optValue1');
        $params = array('param1' => 'paramValue1');
        $path = '/path';

        $expectedArray = array('value');
        $stream = Stream::factory(json_encode($expectedArray));

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('createJsonBody', 'getDefaultHeaders'))
            ->getMock();

        $client->expects($this->any())
            ->method('createJsonBody')
            ->will($this->returnValue(json_encode($params)));

        $client->expects($this->once())
            ->method('getDefaultHeaders')
            ->will($this->returnValue($defaultOptions));

        $body = $encodedBody ? json_encode($params) : $params;

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method($httpMethod)
            ->with(sprintf('%s%s', $apiVersion, $path), $body, array_merge($options, $defaultOptions))
            ->will($this->returnValue(new Response(200, array(), $stream)));

        $client->setHttpClient($httpClient);
        $client->setOption('api_version', $apiVersion);

        $this->assertEquals($expectedArray, $client->$httpMethod('/path', $params, $options));
    }

    /**
     * @test
     * @dataProvider getDefaultRequestHttpMethods
     */
    public function shouldSendRequestWithoutApiVersionToClient($httpMethod, $encodedBody)
    {
        $defaultOptions = array('defaultOption' => 'defaultValue');
        $options = array('option1' => 'optValue1');
        $params = array('param1' => 'paramValue1');
        $path = '/path';

        $expectedArray = array('value');
        $stream = Stream::factory(json_encode($expectedArray));

        $client = $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods(array('createJsonBody', 'getDefaultHeaders'))
            ->getMock();

        $client->expects($this->any())
            ->method('createJsonBody')
            ->will($this->returnValue(json_encode($params)));

        $client->expects($this->once())
            ->method('getDefaultHeaders')
            ->will($this->returnValue($defaultOptions));

        $body = $encodedBody ? json_encode($params) : $params;

        $httpClient = $this->getHttpClientMock();
        $httpClient->expects($this->once())
            ->method($httpMethod)
            ->with($path, $body, array_merge($options, $defaultOptions))
            ->will($this->returnValue(new Response(200, array(), $stream)));

        $client->setHttpClient($httpClient);
        $client->setOption('api_version', null);

        $this->assertEquals($expectedArray, $client->$httpMethod('/path', $params, $options));
    }

    public function getDefaultRequestHttpMethods()
    {
        return array(
            array('delete', true),
            array('get', false),
            array('patch', true),
            array('post', true),
            array('put', true),
        );
    }

    protected function getBitreserveClientMock()
    {
        $methods = array('get', 'post', 'patch', 'put', 'delete', 'request', 'setOption', 'getOption', 'setHeaders');

        return $this->getMockBuilder('Bitreserve\BitreserveClient')
            ->setMethods($methods)
            ->getMock();
    }

    protected function getHttpClientMock()
    {
        $methods = array('get', 'post', 'patch', 'put', 'delete', 'request', 'setOption', 'setHeaders');

        return $this->getMockBuilder('Bitreserve\HttpClient\HttpClientInterface')
            ->setMethods($methods)
            ->getMock();
    }
}
