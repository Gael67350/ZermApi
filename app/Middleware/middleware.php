<?php

use \App\Handler\ErrorHandler;
use \App\Middleware\FormatAPIResponseMiddleware;
use \App\Middleware\SetContentTypeToJsonMiddleware;
use \Slim\Middleware\JwtAuthentication;

$app->add(new JwtAuthentication([
    'path' => '/',
    'passthrough' => '/token',
    'attribute' => 'jwt',
    'secret' => $config['auth']['public_key'],
    'algorithm' => 'RS256',
    'secure' => !$config['app']['debug'],

    'error' => new ErrorHandler(ErrorHandler::STATUS_UNAUTHORIZED)
]));

$app->add(new SetContentTypeToJsonMiddleware());

$app->add(new FormatAPIResponseMiddleware());