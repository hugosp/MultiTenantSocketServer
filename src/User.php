<?php

namespace Hewgo;

use Ratchet\ConnectionInterface;

class User
{
    private $id;
    private $client;

    public function __construct(ConnectionInterface $client, $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getConnectionId()
    {
        return $this->client->resourceId;
    }

    public function send($data)
    {
        if(!is_string($data)) {
            $data = json_encode($data);
        }

        $this->client->send($data);
    }
}
