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


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SetContentTypeToJsonMiddleware {

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        $response = $next($request, $response)->withHeader("Content-Type", "application/json");
        return $response;
    }

}