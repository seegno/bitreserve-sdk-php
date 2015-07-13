<?php

namespace Bitreserve\Paginator;

/**
 * Paginator.
 */
class Paginator
{
    /**
     * Bitreserve client.
     *
     * @var BitreserveClient
     */
    protected $client;

    /**
     * Count.
     *
     * @var int
     */
    protected $count;

    /**
     * Model class.
     *
     * @var string
     */
    protected $model;

    /**
     * Reqest path.
     *
     * @var string
     */
    protected $path;

    /**
     * Request parameters.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Request headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * Range offset.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Range limit.
     *
     * @var int
     */
    protected $limit;

    /**
     * Constructor.
     *
     * @param BitreserveClient $client Bitreserve client.
     * @param string $path Request path.
     * @param array $parameters Request parameters.
     * @param array $headers Request headers.
     */
    public function __construct($client, $path, $parameters = array(), $headers = array(), $limit = 50)
    {
        $this->client = $client;
        $this->path = $path;
        $this->parameters = $parameters;
        $this->headers = $headers;
        $this->limit = $limit;
    }

    /**
     * Get model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set model.
     *
     * @return Paginator
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Count the total number of results.
     *
     * @return int
     */
    public function count()
    {
        $headers = array_merge($this->headers, array('range' => $this->buildRangeHeader(0, 1)));

        try {
            $response = $this->client->get($this->path, $this->parameters, $headers);

            $contentRange = $response->getContentRange();

            $this->count = $contentRange['count'];

            return $this->count;
        } catch (BitreserveException $e) {
            if (412 === $e->getHttpCode() || 416 === $e->getHttpCode()) {
                return 0;
            }

            throw $e;
        }
    }

    /**
     * Get next results page.
     *
     * @return mixed
     */
    public function getNext()
    {
        $range = $this->getNextRange();

        $headers = array_merge($this->headers, array('range' => $this->buildRangeHeader($range['start'], $range['end'])));

        try {
            $response = $this->client->get($this->path, $this->parameters, $headers);

            $contentRange = $response->getContentRange();

            $this->count = $contentRange['count'];
            $this->limit = $contentRange['end'] - $contentRange['start'] + 1;
            $this->offset = $contentRange['end'] + 1;

            return $this->hydrate($response->getContent());
        } catch (BitreserveException $e) {
            if (412 === $e->getHttpCode() || 416 === $e->getHttpCode()) {
                return $this->hydrate(array());
            }

            throw $e;
        }
    }

    /**
     * Check whether the paginator has a next page.
     *
     * @return boolean
     */
    public function hasNext()
    {
        $range = $this->getNextRange();

        $count = $this->count ?: $this->count();

        if ($range['start'] >= $count) {
            return false;
        }

        return true;
    }

    /**
     * Hydrate data results.
     *
     * @param array $data Results data.
     *
     * @return array
     */
    public function hydrate($data)
    {
        if (!$this->model) {
            return $data;
        }

        return array_map(function($object) {
            return new $this->model($this->client, $object);
        }, $data);
    }

    /**
     * Build range header.
     *
     * @param int $start Range start position.
     * @param int $end Range end position.
     *
     * @return string
     */
    protected function buildRangeHeader($start, $end)
    {
        return sprintf('items=%s-%s', $start, $end);;
    }

    /**
     * Get next range positions.
     *
     * @return array
     */
    protected function getNextRange()
    {
        return array(
            'end' => $this->offset + $this->limit - 1,
            'start' => $this->offset,
        );
    }
}
