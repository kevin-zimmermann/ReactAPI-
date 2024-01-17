<?php

//namespace classes;

const HOST = '127.0.0.1';
const DB_NAME = 'reactapi';
const USERNAME = 'root';
const PASSWORD = '';

class Bdd
{

    private $pdo = "mysql:host=" . HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    private $username = USERNAME;

    public function __construct()
    {
//        $this->username = $username;
//        $this->pdo = $pdo;

//
    }
    public function getUsername()
    {
        return $this->username;
    }

    public function getPdo()
    {
        return $this->pdo;
    }
    public function clearInput()
    {
        //Using htmlspecialchars
        foreach ($_POST as $key => $value) {
            if ($key !== 'token') {
                $_POST[$key] = htmlspecialchars($value);
            }
        }
    }

}


