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
use App\Helper\DatabaseHelper;
use App\Http\Response;
use Carbon\Carbon;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/token', function (Request $request, Response $response, array $args) {
    global $config;
    $devices = DatabaseHelper::deviceTableRegistry();
    $params = $request->getQueryParams();

    if (empty($params['uuid']) || empty($params['secret'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    $device = $devices->findByUuid($params['uuid'])->where(['security_token' => $params['secret']])->first();

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

$app->get('/homes[/{id}]', function (Request $request, Response $response, array $args) {
    $homes = DatabaseHelper::homeTableRegistry();

    if (!empty($args['id'])) {
        $home = $homes->findById($args['id'])->first();

        if (empty($home)) {
            throw new Exception("Home not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['homes'] = $home->toArray();
    } else {
        $allHomes = $homes->find()->all();

        $data['count'] = $allHomes->count();
        $data['homes'] = $allHomes->toArray();
    }

    return $response->withData($data);
});

$app->get('/rooms[/{id}]', function (Request $request, Response $response, array $args) {
    $rooms = DatabaseHelper::roomTableRegistry();

    if (!empty($args['id'])) {
        $room = $rooms->findById($args['id'])->first();

        if (empty($room)) {
            throw new Exception("Room not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['rooms'] = $room->toArray();
    } else {
        $allRooms = $rooms->find()->all();

        $data['count'] = $allRooms->count();
        $data['rooms'] = $allRooms->toArray();
    }

    return $response->withData($data);
});

$app->get('/devices[/{uuid}]', function (Request $request, Response $response, array $args) {
    $devices = DatabaseHelper::deviceTableRegistry();

    if (!empty($args['uuid'])) {
        $device = $devices->findByUuid($args['uuid'])->first();

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

$app->get('/devices/{uuid}/features[/{feature_id}]', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $params = $request->getQueryParams();

    if (empty($args['uuid'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    if (!empty($args['feature_id'])) {
        if (isset($params['logical']) && (boolean)$params['logical']) {
            $feature = $deviceFeatures->findByLogicalId($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['Units'])->first();
        } else {
            $feature = $deviceFeatures->findById($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['Units'])->first();
        }

        if (empty($feature)) {
            throw new Exception("Feature not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['features'] = $feature->toArray();
    } else {
        if (isset($params['sensor'])) {
            $sensor = (boolean)$params['sensor'];
            $allFeatures = $deviceFeatures->findByDeviceUuid($args['uuid'])->where(['sensor' => $sensor])->contain(['Units'])->all();
        } else {
            $allFeatures = $deviceFeatures->findByDeviceUuid($args['uuid'])->contain(['Units'])->all();
        }

        $data['count'] = $allFeatures->count();
        $data['features'] = $allFeatures->toArray();
    }

    return $response->withData($data);
});

$app->get('/devices/{uuid}/features/{feature_id}/states[/{state_id}]', function (Request $request, Response $response, array $args) {
    $deviceStates = DatabaseHelper::deviceStateTableRegistry();
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $params = $request->getQueryParams();
    $limit = $params['limit'] ?: 10;

    if (empty($args['uuid']) || empty($args['feature_id'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    if (!empty($args['state_id'])) {
        $state = $deviceStates->findById($args['state_id'])->first();

        if (empty($state)) {
            throw new Exception("No state found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['states'] = $state->toArray();
    } else {
        if (isset($params['logical']) && (boolean)$params['logical']) {
            $feature = $deviceFeatures->findByLogicalId($args['feature_id'])->where(['device_uuid' => $args['uuid']])->first();

            if (!empty($feature)) {
                $args['feature_id'] = $feature->id;
            }
        }

        $allStates = $deviceStates->findByDeviceFeatureId($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['DeviceFeatures'])->order(['created' => 'DESC'])->limit($limit)->all();

        if ($allStates->count() == 0) {
            throw new Exception("No state found", ErrorHandler::STATUS_NOT_FOUND);
        }

        foreach ($allStates as $state) {
            unset($state->device_feature);
        }

        $data['count'] = $allStates->count();
        $data['states'] = $allStates;
    }

    return $response->withData($data);
});

$app->get('/units[/{id}]', function (Request $request, Response $response, array $args) {
    $units = DatabaseHelper::unitTableRegistry();

    if (!empty($args['id'])) {
        $unit = $units->findById($args['id'])->first();

        if (empty($unit)) {
            throw new Exception("Unit not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $data['units'] = $unit->toArray();
    } else {
        $allUnits = $units->find()->all();

        $data['count'] = $allUnits->count();
        $data['units'] = $allUnits->toArray();
    }

    return $response->withData($data);
});

$app->put('/devices/{uuid}/features/{feature_id}/states', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $deviceStates = DatabaseHelper::deviceStateTableRegistry();
    $params = $request->getQueryParams();

    if (empty($args['uuid'] || empty($args['feature_id']))) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    if (isset($params['logical']) && (boolean)$params['logical']) {
        if ($this->jwt->device->uuid == $args['uuid']) {
            $feature = $deviceFeatures->findByLogicalId($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['Units'])->first();
        } else {
            $feature = $deviceFeatures->findByLogicalId($args['feature_id'])->where(['device_uuid' => $args['uuid'], 'sensor' => false])->contain(['Units'])->first();
        }
    } else {
        if ($this->jwt->device->uuid == $args['uuid']) {
            $feature = $deviceFeatures->findById($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['Units'])->first();
        } else {
            $feature = $deviceFeatures->findById($args['feature_id'])->where(['device_uuid' => $args['uuid'], 'sensor' => false])->contain(['Units'])->first();
        }
    }

    if (empty($feature)) {
        throw new Exception("No updatable feature found", ErrorHandler::STATUS_NOT_FOUND);
    }

    if ($params['value'] < $feature->minValue || $params['value'] > $feature->maxValue) {
        throw new Exception("The passed value is not valid", ErrorHandler::STATUS_BAD_REQUEST);
    }

    $newState = $deviceStates->newEntity();
    $newState->value = $params['value'] ?: $feature->default_value;
    $newState->device_feature_id = $feature->id;

    if (!$deviceStates->save($newState)) {
        throw new Exception(null, ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
    }

    $data = $newState->toArray();
    return $response->withData($data)->withStatus(ErrorHandler::STATUS_CREATED);
});

$app->put('/devices/{uuid}/states/reset', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $deviceStates = DatabaseHelper::deviceStateTableRegistry();

    if (empty($args['uuid'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    $features = $deviceFeatures->findByDeviceUuid($args['uuid'])->where(['sensor' => false])->all();

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