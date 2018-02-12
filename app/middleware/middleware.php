<?php

use \App\Handlers\ErrorHandler;
use \App\Middleware\SetContentTypeToJsonMiddleware;
use \Slim\Middleware\JwtAuthentication;

$app->add(new JwtAuthentication([
    'secret' => openssl_pkey_get_public(__DIR__ . "../../certs/" . $config['auth']['public_key']),
    'algorithm' => ['RS256'],
    'secure' => !$config['app']['debug'],

    'error' => new ErrorHandler(ErrorHandler::STATUS_UNAUTHORIZED)
]));

$app->add(new SetContentTypeToJsonMiddleware());