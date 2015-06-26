<?php

namespace Bitreserve\Model;

use Bitreserve\BitreserveClient;
use Bitreserve\Exception\LogicException;

/**
 * Transaction Model.
 */
class Transaction extends BaseModel implements TransactionInterface
{
    /**
     * Id.
     *
     * @var string
     */
    protected $id;

    /**
     * Created at.
     *
     * @var string
     */
    protected $createdAt;

    /**
     * Denomination.
     *
     * @var array
     */
    protected $denomination;

    /**
     * Destination.
     *
     * @var array
     */
    protected $destination;

    /**
     * Message.
     *
     * @var string
     */
    protected $message;

    /**
     * Origin information.
     *
     * @var array
     */
    protected $origin;

    /**
     * Params.
     *
     * @var array
     */
    protected $params;

    /**
     * Refunded by id.
     *
     * @var string.
     */
    protected $refundedById;

    /**
     * Status.
     *
     * @var string
     */
    protected $status;

    /**
     * Type.
     *
     * @var string
     */
    protected $type;

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
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getDenomination()
    {
        return $this->denomination;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefundedById()
    {
        return $this->refundedById;
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        if (empty($this->origin['CardId'])) {
            throw new LogicException('Origin CardId is missing from this transaction');
        }

        if ('pending' !== $this->status) {
            throw new LogicException(sprintf('This transaction cannot be committed, because the current status is "%s"', $this->status));
        }

        $response = $this->client->post(sprintf('/me/cards/%s/transactions/%s/commit', $this->origin['CardId'], $this->id));

        $this->updateFields($response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function cancel()
    {
        if (empty($this->origin['CardId'])) {
            throw new LogicException('Origin CardId is missing from this transaction');
        }

        if ('pending' === $this->status) {
            throw new LogicException('Unable to cancel uncommited transaction');
        }

        if ('waiting' !== $this->status) {
            throw new LogicException(sprintf('This transaction cannot be cancelled, because the current status is %s', $this->status));
        }

        $response = $this->client->post(sprintf('/me/cards/%s/transactions/%s/cancel', $this->origin['CardId'], $this->id));

        $this->updateFields($response->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function resend()
    {
        if (empty($this->origin['CardId'])) {
            throw new LogicException('Origin CardId is missing from this transaction');
        }

        if ('waiting' !== $this->status) {
            throw new LogicException(sprintf('This transaction cannot be resent, because the current status is %s', $this->status));
        }

        $response = $this->client->post(sprintf('/me/cards/%s/transactions/%s/resend', $this->origin['CardId'], $this->id));

        $this->updateFields($response->getContent());
    }
}
