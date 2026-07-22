<?php

/**
 * post.php
 * ---------------------------------------------------------------
 * Déclare toutes les routes HTTP POST de l'application.
 * ---------------------------------------------------------------
 */

$router->map('POST', '/projects/create', 'ProjectController#create', 'projects.create');
$router->map('POST', '/examples/create', 'ExampleController#create', 'examples.create');