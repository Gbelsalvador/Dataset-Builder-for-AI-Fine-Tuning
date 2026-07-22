<?php

namespace App\Controllers;

use App\Models\Example;
use App\Models\Project;
use InvalidArgumentException;
use MongoDB\Driver\Exception\Exception as MongoException;

/**
 * ExampleController
 * ---------------------------------------------------------------
 * Gère la création, la modification, la suppression, le listing et
 * la recherche des exemples d'entraînement au sein d'un projet.
 *
 * Routes associées (voir routes/) :
 *   POST   /examples/create           -> create()
 *   POST   /examples/update/{id}      -> update($id)
 *   POST   /examples/delete/{id}      -> delete($id)
 *   GET    /examples/list             -> list()
 *   GET    /examples/search           -> search()
 * ---------------------------------------------------------------
 */
class ExampleController extends Controller
{
    private Example $exampleModel;
    private Project $projectModel;

    public function __construct()
    {
        $this->exampleModel = new Example();
        $this->projectModel = new Project();
    }

    /**
     * Ajoute un exemple d'entraînement à un projet.
     * La sauvegarde est immédiate (sauvegarde automatique dans MongoDB).
     * Champs requis : project_id, content
     */
    public function create(): void
    {
        $data = $this->getJsonBody() ?: $_POST;

        $errors = $this->validateRequired($data, ['project_id', 'content']);
        if (!empty($errors)) {
            $this->error('Champs manquants.', 422, $errors);
            return;
        }

        try {
            $project = $this->projectModel->findById($data['project_id']);
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
            return;
        }

        if ($project === null) {
            $this->error('Le projet associé est introuvable.', 404);
            return;
        }

        try {
            $id = $this->exampleModel->create([
                'project_id' => $data['project_id'],
                'format'     => $data['format'] ?? $project['format'],
                'content'    => $data['content'],
                'tags'       => $data['tags'] ?? [],
            ]);

            $this->projectModel->incrementExamplesCount($data['project_id'], 1);

            $this->success(['id' => $id], 'Exemple ajouté avec succès.', 201);
        } catch (MongoException $e) {
            $this->error("Erreur lors de l'ajout de l'exemple.", 500, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Modifie un exemple existant (mise à jour partielle).
     */
    public function update(string $id): void
    {
        $data = $this->getJsonBody() ?: $_POST;

        $payload = array_filter([
            'content' => $data['content'] ?? null,
            'tags'    => $data['tags'] ?? null,
            'format'  => $data['format'] ?? null,
        ], static fn ($value) => $value !== null);

        if (empty($payload)) {
            $this->error('Aucune donnée à mettre à jour.', 422);
            return;
        }

        try {
            $updated = $this->exampleModel->update($id, $payload);

            if (!$updated) {
                $this->error('Exemple introuvable ou aucune modification apportée.', 404);
                return;
            }

            $this->success([], 'Exemple mis à jour avec succès.');
        } catch (InvalidArgumentException $e) {
            $this->error("Identifiant d'exemple invalide.", 422);
        }
    }

    /**
     * Supprime un exemple et décrémente le compteur du projet associé.
     */
    public function delete(string $id): void
    {
        try {
            $example = $this->exampleModel->findById($id);

            if ($example === null) {
                $this->error('Exemple introuvable.', 404);
                return;
            }

            $this->exampleModel->delete($id);
            $this->projectModel->incrementExamplesCount((string) $example['project_id'], -1);

            $this->success([], 'Exemple supprimé avec succès.');
        } catch (InvalidArgumentException $e) {
            $this->error("Identifiant d'exemple invalide.", 422);
        }
    }

    /**
     * Liste paginée des exemples d'un projet.
     * Query params : project_id (requis), page (défaut 1), limit (défaut 20)
     */
    public function list(): void
    {
        $projectId = $_GET['project_id'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, (int) ($_GET['limit'] ?? 20));

        if (!$projectId) {
            $this->error('Le paramètre "project_id" est requis.', 422);
            return;
        }

        try {
            $examples = $this->exampleModel->findByProject($projectId, $page, $limit);
            $total = $this->exampleModel->countByProject($projectId);

            $this->success([
                'examples' => $examples,
                'page'     => $page,
                'limit'    => $limit,
                'total'    => $total,
            ]);
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
        }
    }

    /**
     * Recherche full-text d'exemples au sein d'un projet.
     * Query params : project_id (requis), q (requis)
     */
    public function search(): void
    {
        $projectId = $_GET['project_id'] ?? null;
        $query = trim($_GET['q'] ?? '');

        if (!$projectId || $query === '') {
            $this->error('Les paramètres "project_id" et "q" sont requis.', 422);
            return;
        }

        try {
            $results = $this->exampleModel->search($projectId, $query);
            $this->success(['examples' => $results, 'query' => $query]);
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
        }
    }
}