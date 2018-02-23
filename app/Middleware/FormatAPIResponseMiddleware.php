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

namespace App\Middleware;


use App\Handler\ErrorHandler;
use App\Http\Response;
use Psr\Http\Message\ServerRequestInterface;

class FormatAPIResponseMiddleware {

    public function __invoke(ServerRequestInterface $request, Response $response, callable $next) {
        $response = $next($request, $response);
        return self::response($request, $response);
    }

    public static function response(ServerRequestInterface $request, Response $response) {
        $apiData['status'] = $response->getStatusCode();
        $apiData['message'] = $response->getMessage() ?: ErrorHandler::DEFAULT_MESSAGES[$apiData['status']];
        $apiData['results'] = $response->getData();
        $apiData['uri'] = (string)$request->getUri();

        $response->withStatus($apiData['status']);
        $response->getBody()->write(json_encode($apiData, JSON_UNESCAPED_SLASHES));

        return $response;
    }

}