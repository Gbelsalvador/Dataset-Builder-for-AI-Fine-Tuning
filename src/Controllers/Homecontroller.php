<?php

namespace App\Controllers;

/**
 * HomeController
 * ---------------------------------------------------------------
 * Contrôleur "pages" : rend les vues HTML de l'application (par
 * opposition aux autres contrôleurs qui exposent l'API JSON
 * consommée en AJAX par ces mêmes vues).
 * ---------------------------------------------------------------
 */
class HomeController
{
    /**
     * Page d'accueil : liste des projets de dataset.
     */
    public function index(): void
    {
        require dirname(__DIR__) . '/views/projects/index.php';
    }

    /**
     * Atelier d'un projet : gestion des exemples et export.
     * L'existence du projet est vérifiée côté client via
     * GET /projects/show/{id} (ProjectController::show) ; un
     * identifiant invalide affichera une erreur dans la page plutôt
     * qu'un 404 serveur.
     */
    public function project(string $id): void
    {
        require dirname(__DIR__) . '/views/projects/show.php';
    }
}