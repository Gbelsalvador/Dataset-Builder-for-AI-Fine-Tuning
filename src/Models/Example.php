<?php

namespace App\Models;

use App\Config\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection;

/**
 * Example
 * ---------------------------------------------------------------
 * Modèle d'accès aux données pour la collection "examples".
 * Un exemple représente une entrée d'entraînement rattachée à un
 * projet (project_id), dont le contenu ("content") est libre et
 * dépend du format choisi (instruction/input/output, messages...).
 * ---------------------------------------------------------------
 */
class Example
{
    private Collection $collection;

    public function __construct()
    {
        $this->collection = Database::getInstance()->getCollection('examples');
    }

    /**
     * Crée un nouvel exemple et retourne son identifiant.
     */
    public function create(array $data): string
    {
        $document = [
            'project_id' => new ObjectId($data['project_id']),
            'format'     => $data['format'],
            'content'    => $data['content'],
            'tags'       => $data['tags'] ?? [],
            'created_at' => new UTCDateTime(),
            'updated_at' => new UTCDateTime(),
        ];

        $result = $this->collection->insertOne($document);

        return (string) $result->getInsertedId();
    }

    /**
     * Récupère un exemple par son identifiant.
     */
    public function findById(string $id): ?array
    {
        $document = $this->collection->findOne(['_id' => new ObjectId($id)]);

        return $document ? (array) $document : null;
    }

    /**
     * Liste paginée des exemples d'un projet.
     */
    public function findByProject(string $projectId, int $page = 1, int $limit = 20): array
    {
        $cursor = $this->collection->find(
            ['project_id' => new ObjectId($projectId)],
            [
                'sort'  => ['created_at' => -1],
                'skip'  => max(0, ($page - 1) * $limit),
                'limit' => $limit,
            ]
        );

        return $cursor->toArray();
    }

    /**
     * Recherche full-text d'exemples au sein d'un projet
     * (utilise l'index texte "examples_search_index").
     */
    public function search(string $projectId, string $query): array
    {
        $cursor = $this->collection->find([
            'project_id' => new ObjectId($projectId),
            '$text'      => ['$search' => $query],
        ]);

        return $cursor->toArray();
    }

    /**
     * Met à jour un exemple existant.
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
     * Supprime un exemple.
     */
    public function delete(string $id): bool
    {
        $result = $this->collection->deleteOne(['_id' => new ObjectId($id)]);

        return $result->getDeletedCount() > 0;
    }

    /**
     * Supprime tous les exemples rattachés à un projet
     * (utile lors de la suppression d'un projet).
     */
    public function deleteByProject(string $projectId): int
    {
        $result = $this->collection->deleteMany(['project_id' => new ObjectId($projectId)]);

        return $result->getDeletedCount();
    }

    /**
     * Récupère l'intégralité des exemples d'un projet, triés par ordre
     * de création, prêts à être transformés par une classe d'export.
     */
    public function getAllForExport(string $projectId): array
    {
        $cursor = $this->collection->find(
            ['project_id' => new ObjectId($projectId)],
            ['sort' => ['created_at' => 1]]
        );

        return $cursor->toArray();
    }

    /**
     * Compte le nombre d'exemples d'un projet.
     */
    public function countByProject(string $projectId): int
    {
        return $this->collection->countDocuments(['project_id' => new ObjectId($projectId)]);
    }
}