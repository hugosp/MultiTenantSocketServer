<?php

namespace Hewgo;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class MessageBroker implements MessageComponentInterface
{
    private $companys = [];
    private $clients_companies_map = [];
    private $user_it_to_company_map = [];

    public function onOpen(ConnectionInterface $conn)
    {
        // Borde hashas här för att få ut uid & user_id
        $query_string = $conn->httpRequest->getUri()->getQuery();
        parse_str($query_string, $query_params);
        $uid = $query_params['uid'] ?? null;
        $user_id = $query_params['user_id'] ?? null;

        if(empty($uid) || empty($user_id)) {
            return;
        }

        if(empty($this->companys[$uid])) {
            $this->companys[$uid] = new Company($uid);
        }

        $user = new User($conn, $user_id);

        $this->companys[$uid]->addUser($user);

        // spara map för connID
        $this->clients_companies_map[$conn->resourceId] = $uid;

        // spara map för user_id
        $this->user_it_to_company_map[$user_id] = $uid;

        $user->send([
            'type' => 'message',
            'data' => 'User Connnection established.'
        ]);

        echo "New connection! (uid: {$uid} user_id: {$user_id} conn_id: {$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Message received: {$msg}\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $id = $conn->resourceId;
        $uid = $this->clients_companies_map[$id];
        unset($this->clients_companies_map[$id]);

        // ta bort user från company
        $this->companys[$uid]->removeUser($id);

        // Kolla om company är tom, i så fall ta bort den
        if(empty($this->companys[$uid]->getUsers())) {
            unset($this->companys[$uid]);
        }

        echo "Connection {$id} has disconnected from company {$uid}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    public function send($type, $id, $data)
    {
        if($type == 'all') {
            foreach($this->companys as $company) {
                // kolla även intern eller public med $id
                $company->send($data);
            }
            return;
        }

        if($type == 'company') {
            if(empty($this->companys[$id])) {
                return;
            }
            $this->companys[$id]->send($data);
            return;
        }

        if($type == 'user') {
            $company_id = $this->user_it_to_company_map[$id] ?? null;
            if(empty($this->companys[$company_id])) {
                return;
            }

            $user = $this->companys[$company_id]->getUser($id);
            if(empty($user)) {
                return;
            }
            $user->send($data);
            return;
        }

    }
}
