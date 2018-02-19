<?php

use \App\Handler\ErrorHandler;
use \App\Helper\DatabaseHelper;
use Carbon\Carbon;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/token', function (Request $request, Response $response, array $args) {
    global $config;
    $devices = DatabaseHelper::deviceTableRegistry();
    $params = $request->getQueryParams();

    if (empty($params['device_uuid']) || empty($params['device_secret'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    $device = $devices->findByUuid($params['device_uuid'])->where(['security_token' => $params['device_secret']])->first();

    if (empty($device)) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    if (empty($device->jwt_expire_at) || $device->jwt_expire_at < Carbon::now()) {
        $data['message'] = 'Token renewal';
        $device->jwt_expire_at = Carbon::now()->addDay();

        if (!$devices->save($device)) {
            throw new Exception(null, ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
        }
    } else {
        $data['message'] = 'No need to renew the token';
    }

    $iat = Carbon::createFromTimestamp($device->jwt_expire_at->timestamp)->subDay();

    $payload = [
        'iat' => $iat->timestamp,
        'exp' => $device->jwt_expire_at->timestamp,
        'device' => [
            'uuid' => $device->uuid,
            'name' => $device->name
        ]
    ];

    $data['status'] = ErrorHandler::STATUS_SUCCESS;
    $data['data']['token'] = \Firebase\JWT\JWT::encode($payload, $config['auth']['private_key'], 'RS256');
    $data['uri'] = $request->getUri()->getPath();

    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES));
    $response->withStatus(200);

    return $response;
});