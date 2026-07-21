<?php

namespace App\Models;

use App\Config\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;

/**
 * Project
 * ---------------------------------------------------------------
 * Modèle d'accès aux données pour la collection "projects".
 * Un projet regroupe un ensemble d'exemples destinés à un même
 * format d'export (Alpaca, ShareGPT, OpenAI Messages, JSON, JSONL...).
 * ---------------------------------------------------------------
 */
class Project
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = Database::getInstance()->getCollection('projects');
    }

    /**
     * Crée un nouveau projet et retourne son identifiant.
     */
    public function create(array $data): string
    {
        $document = [
            'name'            => $data['name'],
            'description'     => $data['description'] ?? '',
            'format'          => $data['format'],
            'tags'            => $data['tags'] ?? [],
            'examples_count'  => 0,
            'created_at'      => new UTCDateTime(),
            'updated_at'      => new UTCDateTime(),
        ];

        $result = $this->collection->insertOne($document);

        return (string) $result->getInsertedId();
    }

    /**
     * Récupère un projet par son identifiant.
     */
    public function findById(string $id): ?array
    {
        $document = $this->collection->findOne(['_id' => new ObjectId($id)]);

        return $document ? (array) $document : null;
    }

    /**
     * Récupère tous les projets, triés du plus récent au plus ancien.
     */
    public function findAll(array $filters = []): array
    {
        $cursor = $this->collection->find($filters, ['sort' => ['created_at' => -1]]);

        return $cursor->toArray();
    }

    /**
     * Met à jour un projet existant.
     */
    public function update(string $id, array $data): bool
    {
        $data['updated_at'] = new UTCDateTime();

        $result = $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            ['$set' => $data]
        );

        return $result->getModifiedCount() > 0;
    }

    /**
     * Supprime un projet.
     * NB : la suppression des exemples associés doit être gérée par le
     * contrôleur/service appelant (cascade applicative), par exemple en
     * appelant Example::deleteByProject($id) avant ce delete.
     */
    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);

        return $result->getDeletedCount() > 0;
    }

    /**
     * Incrémente (ou décrémente) le compteur d'exemples d'un projet.
     */
    public function incrementExamplesCount(string $id, int $value = 1): void
    {
        $this->collection->updateOne(
            ['_id' => new ObjectId($id)],
            [
                '$inc' => ['examples_count' => $value],
                '$set' => ['updated_at' => new UTCDateTime()],
            ]
        );
    }
}