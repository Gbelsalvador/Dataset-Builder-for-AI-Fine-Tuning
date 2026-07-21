<?php

namespace APP\Databse;
use MongoDB\Client;


class Database{
    private static $db = null;

    public static function getConnectionDB(){
        if(self::$db === null){
            $config = require_once __DIR__ . '/../../config/database.php';

            $client = new Client($config['uri']);

            $self::$db = $client->selectDatabase($config['database']);
        }

        return self::$db;
    }
}