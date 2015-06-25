<?php

namespace Bitreserve\Model;

use Bitreserve\BitreserveClient;
use Bitreserve\Paginator\Paginator;

/**
 * User Model.
 */
class User extends BaseModel implements UserInterface
{
    /**
     * Country.
     *
     * @var string
     */
    protected $country;

    /**
     * Email.
     *
     * @var string
     */
    protected $email;

    /**
     * First name.
     *
     * @var string
     */
    protected $firstName;

    /**
     * Last name.
     *
     * @var string
     */
    protected $lastName;

    /**
     * Name.
     *
     * @var string
     */
    protected $name;

    /**
     * User settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * State.
     *
     * @var string
     */
    protected $state;

    /**
     * User status.
     *
     * @var array
     */
    protected $status;

    /**
     * Username.
     *
     * @var string
     */
    protected $username;

    /**
     * Constructor.
     *
     * @param BitreserveClient $client Bitreserve client.
     * @param array $data User data.
     */
    public function __construct(BitreserveClient $client, $data)
    {
        $this->client = $client;

        $this->updateFields($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getBalances()
    {
        $response = $this->client->get('/me');

        $this->updateFields($response->getContent());

        return $this->balances['currencies'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBalanceByCurrency($currency)
    {
        $response = $this->client->get('/me');

        $this->updateFields($response->getContent());

        foreach ($this->balances['currencies'] as $balanceCurrency => $balance) {
            if ($currency === $balanceCurrency) {
                return $balance;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCardById($id)
    {
        $response = $this->client->get(sprintf('/me/cards/%s', $id));

        return new Card($this->client, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function getCards()
    {
        $response = $this->client->get('/me/cards');

        return array_map(function($card) {
            return new Card($this->client, $card);
        }, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function getCardsByCurrency($currency)
    {
        $response = $this->client->get('/me/cards');

        $cards = array_reduce($response->getContent(), function($cards, $card) use ($currency) {
            if ($currency !== $card['currency']) {
                return $cards;
            }

            $cards[] = new Card($this->client, $card);

            return $cards;
        });

        return $cards;
    }

    /**
     * {@inheritdoc}
     */
    public function getContacts()
    {
        $response = $this->client->get('/me/contacts');

        return array_map(function($contact) {
            return new Contact($this->client, $contact);
        }, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * {@inheritdoc}
     */
    public function getPhones()
    {
        $response = $this->client->get('/me/phones');

        $this->phones = $response->getContent();

        return $this->phones;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettings()
    {
        $response = $this->client->get('/me');

        $this->updateFields($response->getContent());

        return $this->settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalBalance()
    {
        $response = $this->client->get('/me');

        $this->updateFields($response->getContent());

        return array(
            'amount' => $this->balances['total'],
            'currency' => $this->settings['currency'],
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactions($limit = 50)
    {
        $pager = new Paginator($this->client, '/me/transactions', array(), array(), $limit);
        $pager->setModel('Bitreserve\Model\Transaction');

        return $pager;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function createCard($label, $currency)
    {
        $response = $this->client->post('/me/cards', array('label' => $label, 'currency' => $currency));

        return new Card($this->client, $response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $params)
    {
        $response = $this->client->patch('/me', $params);

        $this->updateFields($response->getContent());

        return $this;
    }
}
