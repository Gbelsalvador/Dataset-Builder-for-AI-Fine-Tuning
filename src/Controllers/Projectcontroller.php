<?php

namespace App\Controllers;

use App\Exports\ExporterFactory;
use App\Models\Example;
use App\Models\Project;
use InvalidArgumentException;
use MongoDB\Driver\Exception\Exception as MongoException;

/**
 * ProjectController
 * ---------------------------------------------------------------
 * Gère le cycle de vie des projets de dataset.
 *
 * Routes associées (voir routes/) :
 *   GET    /projects/list           -> index()
 *   GET    /projects/show/{id}      -> show($id)
 *   POST   /projects/create         -> create()
 *   POST   /projects/update/{id}    -> update($id)
 *   POST   /projects/delete/{id}    -> delete($id)
 * ---------------------------------------------------------------
 */
class ProjectController extends Controller
{
    private Project $projectModel;
    private Example $exampleModel;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->exampleModel = new Example();
    }

    /**
     * Liste tous les projets.
     */
    public function index(): void
    {
        try {
            $projects = $this->projectModel->findAll();
            $this->success($projects);
        } catch (MongoException $e) {
            $this->error('Impossible de récupérer les projets.', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Affiche le détail d'un projet.
     */
    public function show(string $id): void
    {
        try {
            $project = $this->projectModel->findById($id);

            if ($project === null) {
                $this->error('Projet introuvable.', 404);
                return;
            }

            $this->success($project);
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
        }
    }

    /**
     * Crée un nouveau projet de dataset.
     * Champs requis : name, format
     */
    public function create(): void
    {
        $data = $this->getJsonBody() ?: $_POST;

        $errors = $this->validateRequired($data, ['name', 'format']);
        if (!empty($errors)) {
            $this->error('Champs manquants.', 422, $errors);
            return;
        }

        if (!in_array($data['format'], ExporterFactory::availableFormats(), true)) {
            $this->error('Format de dataset invalide.', 422, [
                'format' => 'Formats disponibles : ' . implode(', ', ExporterFactory::availableFormats()),
            ]);
            return;
        }

        try {
            $id = $this->projectModel->create([
                'name'        => trim($data['name']),
                'description' => $data['description'] ?? '',
                'format'      => $data['format'],
                'tags'        => $data['tags'] ?? [],
            ]);

            $this->success(['id' => $id], 'Projet créé avec succès.', 201);
        } catch (MongoException $e) {
            $this->error('Erreur lors de la création du projet.', 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Met à jour un projet existant (mise à jour partielle).
     */
    public function update(string $id): void
    {
        $data = $this->getJsonBody() ?: $_POST;

        if (isset($data['format']) && !in_array($data['format'], ExporterFactory::availableFormats(), true)) {
            $this->error('Format de dataset invalide.', 422);
            return;
        }

        $payload = array_filter([
            'name'        => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'format'      => $data['format'] ?? null,
            'tags'        => $data['tags'] ?? null,
        ], static fn ($value) => $value !== null);

        if (empty($payload)) {
            $this->error('Aucune donnée à mettre à jour.', 422);
            return;
        }

        try {
            $updated = $this->projectModel->update($id, $payload);

            if (!$updated) {
                $this->error('Projet introuvable ou aucune modification apportée.', 404);
                return;
            }

            $this->success([], 'Projet mis à jour avec succès.');
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
        }
    }

    /**
     * Supprime un projet ainsi que tous ses exemples associés (cascade applicative).
     */
    public function delete(string $id): void
    {
        try {
            $this->exampleModel->deleteByProject($id);
            $deleted = $this->projectModel->delete($id);

            if (!$deleted) {
                $this->error('Projet introuvable.', 404);
                return;
            }

            $this->success([], 'Projet et exemples associés supprimés avec succès.');
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
        }
    }
}