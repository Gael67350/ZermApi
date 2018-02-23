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