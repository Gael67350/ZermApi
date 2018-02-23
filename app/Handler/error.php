<?php
/**
 *
 *  ZermThings : An API for an IOT manager system (https://www.zermthings.fr)
 *  Copyright (c) 2018 SCION Gael (https://www.gael67350.eu)
 *
 *  Licensed under The MIT License
 *  For full copyright and license information, please see the LICENSE.txt
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright  Copyright (c) 2018 SCION Gael (https://www.gael67350.eu)
 * @link       https://api.zermthings.fr ZermThings Project
 * @since      1.0
 * @license    https://opensource.org/licenses/mit-license.php MIT License
 *
 */

use App\Handler\ErrorHandler;

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