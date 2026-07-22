<?php

namespace App\Controllers;

/**
 * Controller
 * ---------------------------------------------------------------
 * Classe de base pour tous les contrôleurs. Fournit des helpers
 * communs pour lire les requêtes (JSON ou formulaire) et
 * standardiser les réponses JSON envoyées au frontend (AJAX).
 * ---------------------------------------------------------------
 */
abstract class Controller
{
    /**
     * Envoie une réponse JSON et termine le script.
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Réponse de succès standardisée : { success, message, data }.
     */
    protected function success($data = [], string $message = '', int $statusCode = 200): void
    {
        $this->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Réponse d'erreur standardisée : { success, message, errors }.
     */
    protected function error(string $message, int $statusCode = 400, array $errors = []): void
    {
        $this->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Décode le corps JSON de la requête (utilisé pour les appels AJAX
     * en fetch()/axios envoyés avec Content-Type: application/json).
     */
    protected function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Vérifie que les champs requis sont présents et non vides.
     * Retourne un tableau associatif d'erreurs (vide si tout est valide).
     */
    protected function validateRequired(array $data, array $requiredFields): array
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[$field] = "Le champ \"$field\" est requis.";
            }
        }

        return $errors;
    }
}