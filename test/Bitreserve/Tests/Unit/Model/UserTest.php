<?php

namespace Bitreserve\Tests\Unit\Model;

use Bitreserve\BitreserveClient;
use Bitreserve\Model\User;

/**
 * UserTest.
 */
class UserTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnInstanceOfUser()
    {
        $client = $this->getBitreserveClientMock();

        $data = array('firstName' => $this->faker->firstName);

        $user = new User($client, $data);

        $this->assertInstanceOf('Bitreserve\BitreserveClient', $user->getClient());
    }

    /**
     * @test
     */
    public function shouldPerformAUserUpdate()
    {
        $data = array('firstName' => $this->faker->firstName, 'lastName' => $this->faker->lastName);

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('patch')
            ->with('/me',$data)
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $user->update($data);

        $this->assertEquals($data['firstName'], $user->getFirstName());
        $this->assertEquals($data['lastName'], $user->getLastName());
    }

    /**
     * @test
     */
    public function shouldReturnUsername()
    {
        $data = array('username' => $this->faker->username);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['username'], $user->getUsername());
    }

    /**
     * @test
     */
    public function shouldReturnEmail()
    {
        $data = array('email' => $this->faker->email);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['email'], $user->getEmail());
    }

    /**
     * @test
     */
    public function shouldReturnFirstName()
    {
        $data = array('firstName' => $this->faker->firstName);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['firstName'], $user->getFirstName());
    }

    /**
     * @test
     */
    public function shouldReturnLastName()
    {
        $data = array('lastName' => $this->faker->lastName);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['lastName'], $user->getLastName());
    }

    /**
     * @test
     */
    public function shouldReturnName()
    {
        $data = array('name' => $this->faker->name);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['name'], $user->getName());
    }

    /**
     * @test
     */
    public function shouldReturnCountry()
    {
        $data = array('country' => $this->faker->countryCode);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['country'], $user->getCountry());
    }

    /**
     * @test
     */
    public function shouldReturnCurrencies()
    {
        $data = array('currencies' => array('BTC', 'EUR', 'USD'));

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['currencies'], $user->getCurrencies());
    }

    /**
     * @test
     */
    public function shouldReturnState()
    {
        $data = array('state' => $this->faker->state);

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['state'], $user->getState());
    }

    /**
     * @test
     */
    public function shouldReturnStatus()
    {
        $data = array('status' => array(
            'email' => 'ok',
            'phone' => 'pending',
        ));

        $client = $this->getBitreserveClientMock();

        $user = new User($client, $data);

        $this->assertEquals($data['status'], $user->getStatus());
    }

    /**
     * @test
     */
    public function shouldReturnSettings()
    {
        $data = array('settings' => array(
            'currency' => $this->faker->currencyCode,
            'theme' => 'minimalistic',
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me')
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $this->assertEquals($data['settings'], $user->getSettings());
    }

    /**
     * @test
     */
    public function shouldReturnPhones()
    {
        $data = array(
            'id' => $this->faker->uuid,
            'verified' => $this->faker->boolean,
            'primary' => $this->faker->boolean,
            'e164Masked' => '+XXXXXXXXX04',
            'nationalMasked' => '(XXX) XXX-XX04',
            'internationalMasked' => '+X XXX-XXX-XX04',
        );

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me/phones')
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $phones = $user->getPhones();

        $this->assertEquals($data, $phones);
    }

    /**
     * @test
     */
    public function shouldReturnContacts()
    {
        $data = array(array(
            'id' => $this->faker->uuid,
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me/contacts')
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $contacts = $user->getContacts();

        foreach ($contacts as $contact) {
            $this->assertInstanceOf('Bitreserve\Model\Contact', $contact);
        }
    }

    /**
     * @test
     */
    public function shouldReturnBalances()
    {
        $data = array('balances' => array(
            'currencies' => array(
                'EUR' => array(
                    'balance' => $this->faker->randomFloat(2),
                    'amount' => $this->faker->randomFloat(2),
                    'rate' => $this->faker->randomFloat(2, 1, 2),
                    'currency' => 'EUR',
                ),
                'USD' => array(
                    'balance' => $this->faker->randomFloat(2),
                    'amount' => $this->faker->randomFloat(2),
                    'rate' => $this->faker->randomFloat(2, 1, 2),
                    'currency' => 'EUR',
                ),
                'XAU' => array(
                    'balance' => $this->faker->randomFloat(2),
                    'amount' => $this->faker->randomFloat(2),
                    'rate' => $this->faker->randomFloat(2),
                    'currency' => 'EUR',
                ),
            ),
            'total' => $this->faker->randomFloat(2),
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me')
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $balances = $user->getBalances();

        $this->assertEquals($data['balances']['currencies'], $balances);
    }

    /**
     * @test
     * @dataProvider getCurrenciesProvider
     */
    public function shouldReturnOneBalanceByCurrency($currency)
    {
        $data = array('balances' => array(
            'currencies' => array(
                'EUR' => array(
                    'balance' => $this->faker->randomFloat(2),
                    'amount' => $this->faker->randomFloat(2),
                    'rate' => $this->faker->randomFloat(2, 1, 2),
                    'currency' => 'EUR',
                ),
                'USD' => array(
                    'balance' => $this->faker->randomFloat(2),
                    'amount' => $this->faker->randomFloat(2),
                    'rate' => $this->faker->randomFloat(2, 1, 2),
                    'currency' => 'EUR',
                ),
                'XAU' => array(
                    'balance' => $this->faker->randomFloat(2),
                    'amount' => $this->faker->randomFloat(2),
                    'rate' => $this->faker->randomFloat(2),
                    'currency' => 'EUR',
                ),
            ),
            'total' => $this->faker->randomFloat(2),
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me')
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $balances = $user->getBalanceByCurrency($currency);

        $this->assertEquals($data['balances']['currencies'][$currency], $balances);
    }

    /**
     * @test
     */
    public function shouldReturnTotalBalance()
    {
        $data = array(
            'balances' => array(
                'currencies' => array(
                    'EUR' => array(
                        'balance' => $this->faker->randomFloat(2),
                        'amount' => $this->faker->randomFloat(2),
                        'rate' => $this->faker->randomFloat(2, 1, 2),
                        'currency' => 'EUR',
                    ),
                    'USD' => array(
                        'balance' => $this->faker->randomFloat(2),
                        'amount' => $this->faker->randomFloat(2),
                        'rate' => $this->faker->randomFloat(2, 1, 2),
                        'currency' => 'EUR',
                    ),
                    'XAU' => array(
                        'balance' => $this->faker->randomFloat(2),
                        'amount' => $this->faker->randomFloat(2),
                        'rate' => $this->faker->randomFloat(2),
                        'currency' => 'EUR',
                    ),
                ),
                'total' => $this->faker->randomFloat(2),
            ),
            'settings' => array(
                'currency' => $this->faker->currencyCode,
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me')
            ->will($this->returnValue($response));

        $user = new User($client, $data);

        $this->assertEquals(
            array(
                'amount' => $data['balances']['total'],
                'currency' => $data['settings']['currency'],
            ),
            $user->getTotalBalance()
        );
    }

    /**
     * @test
     */
    public function shouldReturnCards()
    {
        $data = array(array(
            'id' => $this->faker->randomDigitNotNull,
            'label' => $this->faker->sentence(2),
            'currency' => $this->faker->currencyCode,
            'balance' => $this->faker->randomFloat(2),
        ), array(
            'id' => $this->faker->randomDigitNotNull,
            'label' => $this->faker->sentence(2),
            'currency' => $this->faker->currencyCode,
            'balance' => $this->faker->randomFloat(2),
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me/cards')
            ->will($this->returnValue($response));

        $user = new User($client, array('username' => $this->faker->username));

        $cards = $user->getCards();

        $this->assertCount(count($data), $cards);

        foreach ($cards as $card) {
            $this->assertInstanceOf('Bitreserve\Model\Card', $card);
        }
    }

    /**
     * @test
     */
    public function shouldReturnCardsByCurrency()
    {
        $expectedCurrency = $this->faker->currencyCode;

        $data = array(array(
            'id' => $this->faker->randomDigitNotNull,
            'label' => $this->faker->sentence(2),
            'currency' =>  $expectedCurrency,
            'balance' => $this->faker->randomFloat(2),
        ), array(
            'id' => $this->faker->randomDigitNotNull,
            'label' => $this->faker->sentence(2),
            'currency' => $this->faker->currencyCode,
            'balance' => $this->faker->randomFloat(2),
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me/cards')
            ->will($this->returnValue($response));

        $user = new User($client, array('username' => $this->faker->username));

        $cards = $user->getCardsByCurrency($expectedCurrency);

        foreach ($cards as $card) {
            $this->assertInstanceOf('Bitreserve\Model\Card', $card);
            $this->assertEquals($expectedCurrency, $card->getCurrency());
        }
    }

    /**
     * @test
     */
    public function shouldCreateNewCard()
    {
        $data = array(
            'id' => $this->faker->randomDigitNotNull,
            'label' => $this->faker->sentence(2),
            'currency' => $this->faker->currencyCode,
        );

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('post')
            ->with('/me/cards')
            ->will($this->returnValue($response));

        $user = new User($client, array('username' => $this->faker->username));

        $card = $user->createCard($data['label'], $data['currency']);

        $this->assertInstanceOf('Bitreserve\Model\Card', $card);

        $this->assertEquals($data['id'], $card->getId());
        $this->assertEquals($data['label'], $card->getLabel());
        $this->assertEquals($data['currency'], $card->getCurrency());
    }

    /**
     * @test
     */
    public function shouldReturnOneCard()
    {
        $data = array(
            'id' => $this->faker->randomDigitNotNull,
            'address' => array(
                'bitcoin' => '145ZeN94MAtTmEgvhXEch3rRgrs7BdD2cY'
            ),
            'label' => $this->faker->sentence(2),
            'currency' => $this->faker->currencyCode,
            'balance' => $this->faker->randomFloat(2),
            'available' => $this->faker->randomFloat(2),
            'lastTransactionAt' => $this->faker->iso8601,
            'position' => $this->faker->randomDigitNotNull,
            'addresses' => array(array(
                'id' => '145ZeN94MAtTmEgvhXEch3rRgrs7BdD2cY',
                'network' => 'bitcoin',
            ))
        );

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with(sprintf('/me/cards/%s', $data['id']))
            ->will($this->returnValue($response));

        $user = new User($client, array('username' => $this->faker->username));

        $card = $user->getCardById($data['id']);

        $this->assertInstanceOf('Bitreserve\Model\Card', $card);
        $this->assertEquals($data['id'], $card->getId());
    }

    /**
     * @test
     */
    public function shouldReturnTransactions()
    {
        $data = array(array(
            'id' => $this->faker->randomDigitNotNull,
            'status' => 'pending',
        ), array(
            'id' => $this->faker->randomDigitNotNull,
            'status' => 'completed',
        ));

        $response = $this->getResponseMock($data);

        $client = $this->getBitreserveClientMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/me/transactions')
            ->will($this->returnValue($response));

        $user = new User($client, array('username' => $this->faker->username));

        $pager = $user->getTransactions();

        $this->assertInstanceOf('Bitreserve\Paginator\Paginator', $pager);

        $transactions = $pager->getNext();

        $this->assertCount(count($data), $transactions);

        foreach ($transactions as $transaction) {
            $this->assertInstanceOf('Bitreserve\Model\Transaction', $transaction);
        }
    }

    public function getCurrenciesProvider()
    {
        return array(
            array('EUR'),
            array('USD'),
            array('XAU'),
        );
    }

    protected function getModelClass()
    {
        return 'Bitreserve\Model\User';
    }
}
