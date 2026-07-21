<?php

require_once __DIR__ . '/../vendor/autoload.php';

$router = new AltoRouter();

// Si tu testes en local avec une URL du type http://localhost/mon-projet/public
// décommente et ajuste la ligne ci-dessous :
// $router->setBasePath('/mon-projet/public');

// 3. Inclusion des routes (situées dans /routes/routes.php)
require_once __DIR__ . '/../routes/get.php';
require_once __DIR__ . '/../routes/post.php';
require_once __DIR__ . '/../routes/update.php';
require_once __DIR__ . '/../routes/delete.php';

$match = $router->match();

if (is_array($match)) {
    list($controllerName, $method) = explode('#', $match['target']);
    
    $controller = "App\\Controllers\\" . $controllerName;
    
    if (class_exists($controller)) {
        $object = new $controller();
        
        if (method_exists($object, $method)) {
            call_user_func_array([$object, $method], $match['params']);
        } else {
            die("Erreur : La méthode '$method' n'existe pas dans le contrôleur '$controller'.");
        }
    } else {
        die("Erreur : Le contrôleur '$controller' n'existe pas.");
    }
} else {
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo "Erreur 404 : Page introuvable.";
}