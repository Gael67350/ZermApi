<?php

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
        $apiData['data'] = $response->getData();
        $apiData['uri'] = (string)$request->getUri();

        $response->withStatus($apiData['status']);
        $response->getBody()->write(json_encode($apiData, JSON_UNESCAPED_SLASHES));

        return $response;
    }

}