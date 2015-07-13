<?php

namespace Bitreserve\Tests\Unit\Model;

use Bitreserve\Model\Transaction;

/**
 * TransactionTest.
 */
class TransactionTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnInstanceOfTransaction()
    {
        $data = array('id' => $this->faker->randomDigitNotNull);

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertInstanceOf('Bitreserve\BitreserveClient', $transaction->getClient());
        $this->assertInstanceOf('Bitreserve\Model\Transaction', $transaction);
    }

    /**
     * @test
     */
    public function shouldReturnId()
    {
        $data = array('id' => $this->faker->uuid);

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['id'], $transaction->getId());
    }

    /**
     * @test
     */
    public function shouldReturnMessage()
    {
        $data = array('message' => '1');

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['message'], $transaction->getMessage());
    }

    /**
     * @test
     */
    public function shouldReturnStatus()
    {
        $data = array('status' => 'pending');

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['status'], $transaction->getStatus());
    }

    /**
     * @test
     */
    public function shouldReturnRefundedById()
    {
        $data = array('refundedById' => '1');

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['refundedById'], $transaction->getRefundedById());
    }

    /**
     * @test
     */
    public function shouldReturnCreatedAt()
    {
        $data = array('createdAt' => '2014-08-27T00:01:11.616Z');

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['createdAt'], $transaction->getCreatedAt());
    }

    /**
     * @test
     */
    public function shouldReturnDenomination()
    {
        $data = array('denomination' => array(
            'rate' => $this->faker->randomFloat(2, 1, 2),
            'amount' => $this->faker->randomFloat,
            'currency' => $this->faker->currencyCode,
        ));

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['denomination'], $transaction->getDenomination());
    }

    /**
     * @test
     */
    public function shouldReturnOrigin()
    {
        $data = array('origin' => array('foo' => 'bar'));

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['origin'], $transaction->getOrigin());
    }

    /**
     * @test
     */
    public function shouldReturnDestination()
    {
        $data = array('destination' => array('foo' => 'bar'));

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['destination'], $transaction->getDestination());
    }

    /**
     * @test
     */
    public function shouldReturnParams()
    {
        $data = array('params' => array('foo' => 'bar'));

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['params'], $transaction->getParams());
    }

    /**
     * @test
     */
    public function shouldReturnType()
    {
        $data = array('type' => 'foobar');

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);

        $this->assertEquals($data['type'], $transaction->getType());
    }

    /**
     * @test
     */
    public function shouldCommit()
    {
        $data = array(
            'id' => $this->faker->uuid,
            'origin' => array(
                'CardId' => $this->faker->uuid,
            ),
            'signature' => '1d326154e7a68c64a650af9d3233d77b8a385ce0',
            'status' => 'pending',
        );

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('post')
            ->with(sprintf('/me/cards/%s/transactions/%s/commit', $data['origin']['CardId'], $data['id']))
            ->will($this->returnValue($response));

        $transaction = new Transaction($client, $data);
        $transaction->commit();

        $this->assertEquals($data['id'], $transaction->getId());
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\LogicException
     */
    public function shouldThrowAnErrorOnCommitWhenStatusIsNotPending()
    {
        $data = array(
            'id' => $this->faker->uuid,
            'origin' => array(
                'CardId' => $this->faker->uuid,
            ),
            'signature' => '1d326154e7a68c64a650af9d3233d77b8a385ce0',
            'status' => 'completed',
        );

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);
        $transaction->commit();
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\LogicException
     */
    public function shouldThrowAnErrorOnCommitWhenCardIdIsNotDefined()
    {
        $data = array(
            'id' => $this->faker->uuid,
        );

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);
        $transaction->commit();
    }

    /**
     * @test
     */
    public function shouldCancel()
    {
        $data = array(
            'id' => $this->faker->uuid,
            'origin' => array(
                'CardId' => $this->faker->uuid,
            ),
            'signature' => '1d326154e7a68c64a650af9d3233d77b8a385ce0',
            'status' => 'waiting',
        );

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('post')
            ->with(sprintf('/me/cards/%s/transactions/%s/cancel', $data['origin']['CardId'], $data['id']))
            ->will($this->returnValue($response));

        $transaction = new Transaction($client, $data);
        $transaction->cancel();

        $this->assertEquals($data['id'], $transaction->getId());
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\LogicException
     */
    public function shouldThrowAnErrorOnCancelWhenStatusIsNotWaiting()
    {
        $data = array(
            'id' => $this->faker->uuid,
            'signature' => '1d326154e7a68c64a650af9d3233d77b8a385ce0',
            'status' => 'completed',
        );

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);
        $transaction->cancel();
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\LogicException
     */
    public function shouldThrowAnErrorOnCancelWhenStatusIsPending()
    {
        $data = array(
            'id' => $this->faker->uuid,
            'signature' => '1d326154e7a68c64a650af9d3233d77b8a385ce0',
            'status' => 'pending',
        );

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);
        $transaction->cancel();
    }

    /**
     * @test
     * @expectedException Bitreserve\Exception\LogicException
     */
    public function shouldThrowAnErrorOnCancelWhenCardIdIsNotDefined()
    {
        $data = array(
            'id' => $this->faker->uuid,
        );

        $client = $this->getBitreserveClientMock();

        $transaction = new Transaction($client, $data);
        $transaction->cancel();
    }

    protected function getModelClass()
    {
        return 'Bitreserve\Model\Transaction';
    }
}
