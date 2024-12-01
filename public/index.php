<?php

if (!file_exists(('./install.lock'))) {
    header("Location: install.php");
    exit;
}

require_once '../core/Database.php';
require_once '../core/Router.php';
require_once '../core/Controller.php';
require_once '../core/Model.php';

// Auto-load controllers and models
spl_autoload_register(function ($class) {
    $paths = ['../app/controllers/', '../app/models/', '../core/'];
    foreach ($paths as $path) {
        if (file_exists("$path$class.php")) {
            require_once "$path$class.php";
            return;
        }
    }

    throw new Exception("Class '$class' not found!");
});

$router = new Router();

$router->add('/', [new UserController(), 'index']);
$router->add('/user/create', [new UserController(), 'create']);
$router->add('/user/list-users', [new UserController(), 'listUsers']);
$router->add('/user/chart', [new UserController(), 'chart']);
$router->add('/user/get-chart-data', [new UserController(), 'getChartData']);

$router->dispatch($_SERVER['REQUEST_URI']);
