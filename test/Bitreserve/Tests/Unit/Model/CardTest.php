<?php

namespace Bitreserve\Tests\Unit\Model;

use Bitreserve\Model\Card;

/**
 * CardTest.
 */
class CardTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnInstanceOfCard()
    {
        $data = array('id' => $this->faker->randomDigitNotNull);

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertInstanceOf('Bitreserve\BitreserveClient', $card->getClient());
        $this->assertInstanceOf('Bitreserve\Model\Card', $card);
    }

    /**
     * @test
     *
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessage Argument 1 passed to Bitreserve\Model\Card::__construct() must be an
     *                           instance of Bitreserve\BitreserveClient, string given
     */
    public function shouldThrowExceptionWhenFirstArgumentIsNotAnInstanceOfBitreserveClient()
    {
        $card = new Card('foo', 'bar');
    }

    /**
     * @test
     */
    public function shouldReturnId()
    {
        $data = array('id' => $this->faker->randomDigitNotNull);

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['id'], $card->getId());
    }

    /**
     * @test
     */
    public function shouldReturnAddress()
    {
        $data = array('address' => array('bitcoin' => '1GpBtJXXa1NdG94cYPGZTc3DfRY2P7EwzH'));

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['address'], $card->getAddress());
    }

    /**
     * @test
     */
    public function shouldReturnLabel()
    {
        $data = array('label' => $this->faker->sentence(3));

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['label'], $card->getLabel());
    }

    /**
     * @test
     */
    public function shouldReturnCurrency()
    {
        $data = array('currency' => $this->faker->currencyCode);

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['currency'], $card->getCurrency());
    }

    /**
     * @test
     */
    public function shouldReturnBalance()
    {
        $data = array('balance' => $this->faker->randomFloat);

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['balance'], $card->getBalance());
    }

    /**
     * @test
     */
    public function shouldReturnAvailable()
    {
        $data = array('available' => $this->faker->randomFloat);

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['available'], $card->getAvailable());
    }

    /**
     * @test
     */
    public function shouldReturnLastTransactionAt()
    {
        $data = array('lastTransactionAt' => '2014-09-24T18:11:53.561Z');

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['lastTransactionAt'], $card->getLastTransactionAt());
    }

    /**
     * @test
     */
    public function shouldReturnSettings()
    {
        $data = array('settings' => array('position' => $this->faker->randomDigitNotNull, 'starred' => true));

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['settings'], $card->getSettings());
    }

    /**
     * @test
     */
    public function shouldReturnAddresses()
    {
        $data = array('addresses' => array(array('id' => '1GpBtJXXa1NdG94cYPGZTc3DfRY2P7EwzH', 'network' => 'bitcoin')));

        $client = $this->getBitreserveClientMock();

        $card = new Card($client, $data);

        $this->assertEquals($data['addresses'], $card->getAddresses());
    }

    /**
     * @test
     */
    public function shouldReturnTransactions()
    {
        $data = array(array(
            'id' => 'a97bb994-6e24-4a89-b653-e0a6d0bcf634',
            'status' => 'pending',
        ), array(
            'id' => 'b97bb994-6e24-4a89-b653-e0a6d0bcf635',
            'status' => 'completed',
        ));

        $response = $this->getResponseMock($data);

        $cardData = array('id' => 'ade869d8-7913-4f67-bb4d-72719f0a2be0');

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with(sprintf('/me/cards/%s/transactions', $cardData['id']))
            ->will($this->returnValue($response));

        $card = new Card($client, $cardData);

        $pager = $card->getTransactions();

        $this->assertInstanceOf('Bitreserve\Paginator\Paginator', $pager);

        $transactions = $pager->getNext();

        $this->assertCount(count($data), $transactions);

        foreach ($transactions as $transaction) {
            $this->assertInstanceOf('Bitreserve\Model\Transaction', $transaction);
        }
    }

    /**
     * @test
     */
    public function shouldCreateNewTransaction()
    {
        $cardData = array('id' => 'ade869d8-7913-4f67-bb4d-72719f0a2be0');

        $postData = array(
            'destination' => $this->faker->email,
            'denomination' => array(
                'amount' => $this->faker->randomFloat,
                'currency' => $this->faker->currencyCode,
        ));

        $data = array(
            'id' => 'a97bb994-6e24-4a89-b653-e0a6d0bcf634',
            'status' => 'pending',
        );

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('post')
            ->with(sprintf('/me/cards/%s/transactions', $cardData['id']), $postData)
            ->will($this->returnValue($response));

        $card = new Card($client, $cardData);

        $transaction = $card->createTransaction($postData['destination'], $postData['denomination']['amount'], $postData['denomination']['currency']);

        $this->assertInstanceOf('Bitreserve\Model\Transaction', $transaction);
        $this->assertEquals($data['id'], $transaction->getId());
    }

    protected function getModelClass()
    {
        return 'Bitreserve\Model\Card';
    }
}
