<?php

$config = include(__DIR__ . "/app/Config/config.php");

date_default_timezone_set($config['app']['timezone']);

$config['auth']['public_key'] = file_get_contents(__DIR__ . "/certs/" . $config['auth']['public_key']);
$config['auth']['private_key'] = file_get_contents(__DIR__ . "/certs/" . $config['auth']['private_key']);

require __DIR__ . "/vendor/autoload.php";

$app = new Slim\App([
    "settings" => [
        "displayErrorDetails" => $config['app']['debug'],
        "debug" => $config['app']['debug'],

        "addContentLengthHeader" => false,
        "determineRouteBeforeAppMiddleware" => true,
    ]
]);

\Cake\Datasource\ConnectionManager::setConfig('default', [
    'className' => 'Cake\Database\Connection',
    'driver' => $config['database']['driver'],
    'host' => $config['database']['host'],
    'database' => $config['database']['schema'],
    'username' => $config['database']['username'],
    'password' => $config['database']['password']
]);

require __DIR__ . "/app/Http/http.php";

require __DIR__ . "/app/Handler/error.php";

require __DIR__ . "/app/Middleware/middleware.php";

require __DIR__ . "/app/Route/api.php";

$app->run();