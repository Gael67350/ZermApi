<?php

$config = include(__DIR__ . "/app/Config/config.php");

date_default_timezone_set($config['app']['timezone']);

require __DIR__ . "/vendor/autoload.php";

$app = new Slim\App([
    "settings" => [
        "displayErrorDetails" => $config['app']['debug'],
        "debug" => $config['app']['debug'],

        "addContentLengthHeader" => false
    ]
]);

require __DIR__ . "/app/Handler/error.php";

require __DIR__ . "/app/Middleware/middleware.php";

$app->run();