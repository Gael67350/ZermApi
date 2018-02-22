<?php

use \App\Handler\ErrorHandler;

$container = $app->getContainer();

if (!$config['app']['debug']) {
    $container['phpErrorHandler'] = function () {
        return new ErrorHandler(ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
    };
}

$container['errorHandler'] = function () {
    return new ErrorHandler();
};

$container['notFoundHandler'] = function () {
    return new ErrorHandler(ErrorHandler::STATUS_NOT_FOUND);
};

$container['notAllowedHandler'] = function () {
    return new ErrorHandler(ErrorHandler::STATUS_NOT_ALLOWED);
};