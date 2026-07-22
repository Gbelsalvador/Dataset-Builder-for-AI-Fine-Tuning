<?php

/**
 * update.php
 * ---------------------------------------------------------------
 * Déclare toutes les routes HTTP PUT 
 *
 * NB : les identifiants MongoDB (ObjectId) sont des chaînes
 * hexadécimales de 24 caractères, d'où l'utilisation du type [h].
 * ---------------------------------------------------------------
 */

$router->map('PUT', '/projects/update/[h:id]', 'ProjectController#update', 'projects.update');
$router->map('PUT', '/examples/update/[h:id]', 'ExampleController#update', 'examples.update');