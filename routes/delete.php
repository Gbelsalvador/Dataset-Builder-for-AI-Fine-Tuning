<?php

/**
 * delete.php
 * ---------------------------------------------------------------
 * Déclare toutes les routes HTTP DELETE 
 * ---------------------------------------------------------------
 */

$router->map('DELETE', '/projects/delete/[h:id]', 'ProjectController#delete', 'projects.delete');
$router->map('DELETE', '/examples/delete/[h:id]', 'ExampleController#delete', 'examples.delete');