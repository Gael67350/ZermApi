<?php

namespace App\Handler;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\AbstractHandler;

class ErrorHandler extends AbstractHandler {

    const STATUS_SUCCESS = 200;

    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_IM_A_TEAPOT = 418;
    const STATUS_TOO_MANY_REQUESTS = 429;

    const STATUS_INTERNAL_SERVER_ERROR = 500;

    private $defaultMessages = [
        self::STATUS_SUCCESS => "OK",

        self::STATUS_BAD_REQUEST => "Bad request",
        self::STATUS_UNAUTHORIZED => "Authentication is required to access to this resource",
        self::STATUS_FORBIDDEN => "Access forbidden",
        self::STATUS_NOT_FOUND => "Resource not found",
        self::STATUS_IM_A_TEAPOT => "I'm a teapot",
        self::STATUS_TOO_MANY_REQUESTS => "Too many request",

        self::STATUS_INTERNAL_SERVER_ERROR => "Internal server error"
    ];

    private $status;
    private $message;

    public function __construct($status = null, $message = null) {
        $this->status = $status ?: 404;
        $this->message = $message ?: $this->defaultMessages[$this->status];
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $arguments) {
        $data['status'] = $this->status;
        $data['message'] = $this->message;
        $data['uri'] = $request->getUri()->getPath();
        return $response
            ->withStatus($this->status)
            ->write(json_encode($data));
    }

}