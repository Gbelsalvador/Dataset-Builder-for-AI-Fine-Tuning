<?php

namespace App\Config;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database as MongoDatabase;

/**
 * Database
 * ---------------------------------------------------------------
 * Gère la connexion à MongoDB via le driver officiel mongodb/mongodb.
 * Implémentée en Singleton pour ne créer qu'une seule connexion
 * durant le cycle de vie d'une requête.
 * ---------------------------------------------------------------
 */
class Database
{
    private static ?Database $instance = null;

    private Client $client;
    private MongoDatabase $database;

    private function __construct()
    {
        $uri = self::env('MONGO_URI', 'mongodb://127.0.0.1:27017');
        $dbName = self::env('MONGO_DB_NAME', 'dataset_builder');

        $this->client = new Client($uri);
        $this->database = $this->client->selectDatabase($dbName);
    }

    /**
     * Retourne l'instance unique de connexion.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retourne l'objet base de données MongoDB.
     */
    public function getDatabase(): MongoDatabase
    {
        return $this->database;
    }

    /**
     * Raccourci pour sélectionner une collection.
     */
    public function getCollection(string $name): Collection
    {
        return $this->database->selectCollection($name);
    }

    /**
     * Petit helper pour lire une variable d'environnement avec valeur
    **/
    private static function env(string $key, string $default): string
    {
        $value = $_ENV[$key] ?? getenv($key);

        return $value !== false && $value !== null ? (string) $value : $default;
    }
}