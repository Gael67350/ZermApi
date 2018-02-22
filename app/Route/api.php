<?php

use \App\Handler\ErrorHandler;
use \App\Helper\DatabaseHelper;
use \App\Http\Response;
use Carbon\Carbon;
use \Psr\Http\Message\ServerRequestInterface as Request;

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
        $response->withMessage('Token renewed');
        $device->jwt_expire_at = Carbon::now()->addDay();

        if (!$devices->save($device)) {
            throw new Exception(null, ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
        }
    } else {
        $response->withMessage('No need to renew the token');
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

    $data['token'] = \Firebase\JWT\JWT::encode($payload, $config['auth']['private_key'], 'RS256');
    return $response->withData($data);
});

$app->get('/homes', function (Request $request, Response $response, array $args) {
    $homes = DatabaseHelper::homeTableRegistry();
    $allHomes = $homes->find()->all();

    $data['count'] = $allHomes->count();
    $data['homes'] = $allHomes->toArray();

    return $response->withData($data);
});

$app->get('/rooms', function (Request $request, Response $response, array $args) {
    $rooms = DatabaseHelper::roomTableRegistry();
    $allRooms = $rooms->find()->all();

    $data['count'] = $allRooms->count();
    $data['rooms'] = $allRooms->toArray();

    return $response->withData($data);
});

$app->get('/devices', function (Request $request, Response $response, array $args) {
    $devices = DatabaseHelper::deviceTableRegistry();
    $params = $request->getQueryParams();

    if (!empty($params['device_uuid'])) {
        $device = $devices->findByUuid($params['device_uuid'])->first();

        if (empty($device)) {
            throw new Exception("Device not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['devices'] = $device->toArray();
    } else {
        $allDevices = $devices->find()->all();

        $data['count'] = $allDevices->count();
        $data['devices'] = $allDevices->toArray();
    }

    return $response->withData($data);
});

$app->get('/devices/features', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $params = $request->getQueryParams();

    if (empty($params['device_uuid'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    if (!empty($params['feature_id'])) {
        $feature = $deviceFeatures->find()->where(['device_uuid' => $params['device_uuid'], 'deviceFeatures.id' => $params['feature_id']])->contain(['Units'])->first();

        if (empty($feature)) {
            throw new Exception("Feature not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['features'] = $feature->toArray();
    } else {
        $allFeatures = $deviceFeatures->findByDeviceUuid($params['device_uuid'])->contain(['Units'])->all();

        $data['count'] = $allFeatures->count();
        $data['features'] = $allFeatures->toArray();
    }

    return $response->withData($data);
});

$app->get('/devices/features/states', function (Request $request, Response $response, array $args) {
    $deviceStates = DatabaseHelper::deviceStateTableRegistry();
    $params = $request->getQueryParams();

    if (empty($params['device_uuid']) || empty($params['feature_id'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    $allStates = $deviceStates->findByDeviceFeatureId($params['feature_id'])->where(['device_uuid' => $params['device_uuid']])->contain(['DeviceFeatures'])->order(['created' => 'DESC'])->limit(10)->all();

    if ($allStates->count() == 0) {
        throw new Exception("No state found", ErrorHandler::STATUS_NOT_FOUND);
    }

    foreach ($allStates as $state) {
        unset($state->device_feature);
    }

    $data['count'] = $allStates->count();
    $data['states'] = $allStates;

    return $response->withData($data);
});

$app->get('/units', function (Request $request, Response $response, array $args) {
    $units = DatabaseHelper::unitTableRegistry();
    $allUnits = $units->find()->all();

    $data['count'] = $allUnits->count();
    $data['units'] = $allUnits->toArray();

    return $response->withData($data);
});

$app->put('/devices/features/states', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $deviceStates = DatabaseHelper::deviceStateTableRegistry();
    $params = $request->getQueryParams();

    if (empty($params['device_uuid'] || empty($params['feature_id']))) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    $feature = $deviceFeatures->findById($params['feature_id'])->where(['device_uuid' => $params['device_uuid'], 'sensor' => false])->first();

    if (empty($feature)) {
        throw new Exception("No updatable feature found", ErrorHandler::STATUS_NOT_FOUND);
    }

    $newState = $deviceStates->newEntity();
    $newState->value = $params['value'] ?: $feature->default_value;
    $newState->device_feature_id = $params['feature_id'];

    if (!$deviceStates->save($newState)) {
        throw new Exception(null, ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
    }

    $data = $newState->toArray();
    return $response->withData($data)->withStatus(ErrorHandler::STATUS_CREATED);
});

$app->put('/devices/states', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $deviceStates = DatabaseHelper::deviceStateTableRegistry();
    $params = $request->getQueryParams();

    if (empty($params['device_uuid'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    $features = $deviceFeatures->findByDeviceUuid($params['device_uuid'])->where(['sensor' => false])->all();

    if ($features->count() == 0) {
        throw new Exception("No updatable feature found", ErrorHandler::STATUS_NOT_FOUND);
    }

    $data = [];
    $index = 0;

    foreach ($features as $feature) {
        $newState = $deviceStates->newEntity();
        $newState->value = $feature->default_value;
        $newState->device_feature_id = $feature->id;

        if (!$deviceStates->save($newState)) {
            throw new Exception(null, ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
        }

        $data[$index] = $newState->toArray();
        $index++;
    }

    return $response->withData($data)->withStatus(ErrorHandler::STATUS_CREATED);
});