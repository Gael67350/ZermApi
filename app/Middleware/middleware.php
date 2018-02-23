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
use App\Middleware\FormatAPIResponseMiddleware;
use App\Middleware\SetContentTypeToJsonMiddleware;
use Slim\Middleware\JwtAuthentication;

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