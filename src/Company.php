<?php

namespace Hewgo;

class Company
{
    private $id;
    private $users =[];

    public function __construct(int $id)
    {
        $this->id = $id;
    }


    public function getUid()
    {
        return $this->id;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getUser($id)
    {
        return $this->users[$id] ?? null;
    }

    public function addUser(User $user)
    {
        $this->users[$user->getId()] = $user;
    }

    public function removeUser($connection_id)
    {
        foreach($this->users as $user) {
            if($user->getConnectionId() == $connection_id) {
                unset($this->users[$user->getId()]);
            }
        }
    }

    public function send($data)
    {
        foreach($this->users as $user) {
            $user->send($data);
        }
    }
}
