<?php

namespace App\Controllers;

use App\Exports\ExporterFactory;
use App\Models\Example;
use App\Models\Project;
use InvalidArgumentException;

/**
 * ExportController
 * ---------------------------------------------------------------
 * Gère la prévisualisation et le téléchargement du dataset dans le
 * format d'export choisi, en s'appuyant sur ExporterFactory
 * (voir exports/).
 *
 * Routes associées (voir routes/) :
 *   GET /export/preview/{format}?project_id=...  -> preview($format)
 *   GET /export/{format}?project_id=...           -> download($format)
 * ---------------------------------------------------------------
 */
class ExportController extends Controller
{
    private Example $exampleModel;
    private Project $projectModel;

    public function __construct()
    {
        $this->exampleModel = new Example();
        $this->projectModel = new Project();
    }

    /**
     * Retourne un aperçu JSON du dataset exporté (sans déclencher le
     * téléchargement), utilisé par la vue de prévisualisation avant export.
     * Query params : project_id (requis), limit (optionnel, défaut 10)
     */
    public function preview(string $format): void
    {
        $projectId = $_GET['project_id'] ?? null;
        $limit = max(1, (int) ($_GET['limit'] ?? 10));

        if (!$projectId) {
            $this->error('Le paramètre "project_id" est requis.', 422);
            return;
        }

        try {
            $project = $this->projectModel->findById($projectId);
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
            return;
        }

        if ($project === null) {
            $this->error('Projet introuvable.', 404);
            return;
        }

        try {
            $exporter = ExporterFactory::make($format);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422, [
                'available_formats' => ExporterFactory::availableFormats(),
            ]);
            return;
        }

        $allExamples = $this->exampleModel->getAllForExport($projectId);
        $sample = array_slice($allExamples, 0, $limit);

        $this->success([
            'format'         => $format,
            'total_examples' => count($allExamples),
            'preview_count'  => count($sample),
            'content'        => $exporter->export($sample),
        ]);
    }

    /**
     * Génère et télécharge le fichier complet du dataset dans le format
     * demandé — prêt à l'emploi, sans transformation supplémentaire.
     * Query params : project_id (requis)
     */
    public function download(string $format): void
    {
        $projectId = $_GET['project_id'] ?? null;

        if (!$projectId) {
            $this->error('Le paramètre "project_id" est requis.', 422);
            return;
        }

        try {
            $project = $this->projectModel->findById($projectId);
        } catch (InvalidArgumentException $e) {
            $this->error('Identifiant de projet invalide.', 422);
            return;
        }

        if ($project === null) {
            $this->error('Projet introuvable.', 404);
            return;
        }

        try {
            $exporter = ExporterFactory::make($format);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422, [
                'available_formats' => ExporterFactory::availableFormats(),
            ]);
            return;
        }

        $examples = $this->exampleModel->getAllForExport($projectId);

        if (empty($examples)) {
            $this->error('Ce projet ne contient aucun exemple à exporter.', 422);
            return;
        }

        $content = $exporter->export($examples);
        $filename = $this->buildFilename((string) $project['name'], $exporter->getFileExtension());

        // Archive une copie du fichier exporté dans storage/exports/
        $this->archiveExport($filename, $content);

        header('Content-Type: ' . $exporter->getMimeType());
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    /**
     * Construit un nom de fichier propre à partir du nom du projet.
     */
    private function buildFilename(string $projectName, string $extension): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $projectName), '-'));
        $timestamp = date('Ymd-His');

        return sprintf('%s-%s.%s', $slug ?: 'dataset', $timestamp, $extension);
    }

    /**
     * Enregistre une copie du fichier exporté dans storage/exports/.
     */
    private function archiveExport(string $filename, string $content): void
    {
        $directory = dirname(__DIR__) . '/storage/exports';

        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($directory . '/' . $filename, $content);
    }
}