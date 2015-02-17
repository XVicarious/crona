<?php

namespace crona;

class Session
{

    private $mySession;

    public function __construct()
    {
        session_start();
        $this->mySession = $_SESSION;
    }

    public function getLastAction()
    {
        return $_SESSION['lastAction'];
    }

    public function getUserId()
    {
        return $_SESSION['userId'];
    }
}
