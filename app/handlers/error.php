<?php

use \App\Handlers\ErrorHandler;

$container = $app->getContainer();

$container['errorHandler'] = function () {
    return new ErrorHandler();
};

$container['phpErrorHandler'] = function () {
    return new ErrorHandler(ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
};

$container['notFoundHandler'] = function () {
    return new ErrorHandler(ErrorHandler::STATUS_NOT_FOUND);
};