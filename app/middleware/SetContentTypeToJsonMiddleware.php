<?php

namespace App\Middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SetContentTypeToJsonMiddleware {

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next) {
        $response = $next($request, $response)->withHeader("Content-Type", "application/json");
        return $response;
    }

}