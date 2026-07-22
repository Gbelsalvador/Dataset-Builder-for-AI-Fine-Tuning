<?php
/**
 * get.php
 * ---------------------------------------------------------------
 * Déclare toutes les routes HTTP GET de l'application
 * Cible : "NomDuController#methode" (namespace App\Controllers ajouté
 * automatiquement dans public/index.php).
 * ---------------------------------------------------------------
 */

// Type personnalisé pour les clés de format d'export (ex: "openai_messages",
// "instruction_output") qui contiennent des underscores, non couverts par
// le type alphanumérique [a] natif d'AltoRouter.
$router->addMatchTypes(['format' => '[a-zA-Z_]++']);

// -----------------------------------------------------------------
// Projets
// -----------------------------------------------------------------
$router->map('GET', '/projects/list', 'ProjectController#index', 'projects.list');
$router->map('GET', '/projects/show/[h:id]', 'ProjectController#show', 'projects.show');

// -----------------------------------------------------------------
// Exemples d'entraînement
// -----------------------------------------------------------------
$router->map('GET', '/examples/list', 'ExampleController#list', 'examples.list');
$router->map('GET', '/examples/search', 'ExampleController#search', 'examples.search');

// -----------------------------------------------------------------
// Export du dataset
// -----------------------------------------------------------------
// IMPORTANT : la route "preview" doit être déclarée AVANT la route
// générique "/export/[format:format]" pour ne pas être capturée par erreur.
$router->map('GET', '/export/preview/[format:format]', 'ExportController#preview', 'export.preview');
$router->map('GET', '/export/[format:format]', 'ExportController#download', 'export.download');