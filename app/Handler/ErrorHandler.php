<?php

namespace App\Handler;


use App\Http\Response;
use App\Middleware\FormatAPIResponseMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\AbstractHandler;

class ErrorHandler extends AbstractHandler {

    const STATUS_SUCCESS = 200;
    const STATUS_CREATED = 201;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_NOT_ALLOWED = 405;
    const STATUS_IM_A_TEAPOT = 418;
    const STATUS_TOO_MANY_REQUESTS = 429;

    const STATUS_INTERNAL_SERVER_ERROR = 500;

    const DEFAULT_MESSAGES = [
        self::STATUS_SUCCESS => "OK",
        self::STATUS_CREATED => "OK",

        self::STATUS_BAD_REQUEST => "Bad request",
        self::STATUS_UNAUTHORIZED => "Authentication is required to access to this resource",
        self::STATUS_FORBIDDEN => "Access forbidden",
        self::STATUS_NOT_ALLOWED => "Method Not Allowed",
        self::STATUS_NOT_FOUND => "Resource not found",
        self::STATUS_IM_A_TEAPOT => "I'm a teapot",
        self::STATUS_TOO_MANY_REQUESTS => "Too many request",

        self::STATUS_INTERNAL_SERVER_ERROR => "Internal server error"
    ];

    private $status;
    private $message;

    public function __construct($status = null, $message = null) {
        $this->status = $status ?: 404;
        $this->message = $message ?: self::DEFAULT_MESSAGES[$this->status];
    }

    public function __invoke(ServerRequestInterface $request, Response $response, $args = null) {
        if ($args instanceof \Exception) {
            $this->status = $args->getCode();
            $this->message = $args->getMessage() ?: self::DEFAULT_MESSAGES[$this->status];

            return FormatAPIResponseMiddleware::response(
                $request,
                $response
                    ->withMessage($this->message)
                    ->withStatus($this->status)
                    ->withHeader("Content-Type", "application/json")
            );
        }

        return $response->withStatus($this->status)->withHeader("Content-Type", "application/json");
    }

}