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

/**
 * @SWG\Swagger(
 *      host="api.zermthings.fr",
 *      schemes={"https"},
 *      produces={"application/json"},
 *      consumes={"application/json"},
 *      @SWG\Info(
 *          title="Zermthings API",
 *          version="1.0",
 *          description="An API for ZermThings domotic project",
 *          @SWG\Contact(
 *              email="contact@zermthings.fr"
 *          ),
 *          @SWG\License(
 *              name="MIT License",
 *              url="https://opensource.org/licenses/mit-license.php"
 *          )
 *      )
 * )
 *
 * @SWG\Tag(
 *      name="Authentication",
 *      description="Everything about authentication",
 * )
 *
 * @SWG\Tag(
 *      name="Homes",
 * )
 *
 * @SWG\Tag(
 *      name="Rooms",
 * )
 *
 * @SWG\Tag(
 *      name="Devices",
 * )
 *
 * @SWG\Tag(
 *      name="Units",
 * )
 */

/**
 * @SWG\Get(
 *     path="/token",
 *     description="Allows authentication of connected devices to the API",
 *     operationId="auth",
 *     tags={"Authentication"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="query",
 *          description="The unique identifier assigned to a device",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Parameter(
 *          name="secret",
 *          in="query",
 *          description="The secret token assigned to a device",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="JWT token successfully delivered"
 *      ),
 *     @SWG\Response(
 *          response=400,
 *          description="Invalid parameters",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      ),
 *     @SWG\Response(
 *          response="default",
 *          description="Unexpected error",
 *          @SWG\Schema(
 *             ref="#/definitions/ErrorModel"
 *         )
 *      )
 * )
 */
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

/**
 * @SWG\Get(
 *     path="/homes",
 *     description="Returns all the homes from the system",
 *     operationId="findHomes",
 *     tags={"Homes"},
 *     @SWG\Response(
 *          response=200,
 *          description="List of homes response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No home to show",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 *
 * @SWG\Get(
 *     path="/homes/{id}",
 *     description="Returns the home with the specified ID",
 *     operationId="findHomeById",
 *     tags={"Homes"},
 *     @SWG\Parameter(
 *          name="id",
 *          in="path",
 *          description="ID of a home to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="Home response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="Home not found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *         )
 *      )
 * )
 */
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

/**
 * @SWG\Get(
 *     path="/rooms",
 *     description="Returns all the rooms from the system",
 *     operationId="findRooms",
 *     tags={"Rooms"},
 *     @SWG\Response(
 *          response=200,
 *          description="List of rooms response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No room to show",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      ),
 * )
 *
 * @SWG\Get(
 *     path="/rooms/{id}",
 *     description="Returns the room with the specified ID",
 *     operationId="findRoomById",
 *     tags={"Rooms"},
 *     @SWG\Parameter(
 *          name="id",
 *          in="path",
 *          description="ID of a room to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="Room response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="Room not found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
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

/**
 * @SWG\Get(
 *     path="/devices",
 *     description="Returns all the devices from the system",
 *     operationId="findDevices",
 *     tags={"Devices"},
 *     @SWG\Response(
 *          response=200,
 *          description="List of devices response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No device to show",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 *
 * @SWG\Get(
 *     path="/devices/{uuid}",
 *     description="Returns the device with the specified ID",
 *     operationId="findDeviceByUuid",
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="Device response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="Device not found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
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

/**
 * @SWG\Get(
 *     path="/devices/{uuid}/features",
 *     description="Returns all the features of a device",
 *     operationId="findDeviceFeatures",
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Parameter(
 *          name="sensor",
 *          in="query",
 *          description="Filter the sensors in the results",
 *          required=false,
 *          type="boolean"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="List of features response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No feature to show",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 *
 * @SWG\Get(
 *     path="/devices/{uuid}/features/{feature_id}",
 *     description="Returns all the features of a device with the specified ID",
 *     operationId="findDeviceFeatureById",
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Parameter(
 *          name="feature_id",
 *          in="path",
 *          description="ID of a device feature to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *      @SWG\Parameter(
 *          name="logical",
 *          in="query",
 *          description="Search a device feature by the internal ID of a feature",
 *          required=false,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="Feature response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="Feature not found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
$app->get('/devices/{uuid}/features[/{feature_id}]', function (Request $request, Response $response, array $args) {
    $deviceFeatures = DatabaseHelper::deviceFeatureTableRegistry();
    $params = $request->getQueryParams();

    if (empty($args['uuid'])) {
        throw new Exception(null, ErrorHandler::STATUS_BAD_REQUEST);
    }

    if (!empty($args['feature_id'])) {
        if (isset($params['logical']) && (boolean)$params['logical']) {
            $feature = $deviceFeatures->findByLogicalId($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['Units', 'DeviceStates' => function ($q) {
                return $q->order(['created' => 'DESC']);
            }])->first();
        } else {
            $feature = $deviceFeatures->findById($args['feature_id'])->where(['device_uuid' => $args['uuid']])->contain(['Units', 'DeviceStates' => function ($q) {
                return $q->order(['created' => 'DESC']);
            }])->first();
        }

        if (empty($feature)) {
            throw new Exception("Feature not found", ErrorHandler::STATUS_NOT_FOUND);
        }

        $feature->currentValue = $feature->device_states[0]->value;
        unset($feature->device_states);

        $data['features'] = $feature->toArray();
    } else {
        if (isset($params['sensor'])) {
            $sensor = (boolean)$params['sensor'];
            $allFeatures = $deviceFeatures->findByDeviceUuid($args['uuid'])->where(['sensor' => $sensor])->contain(['Units', 'DeviceStates' => function ($q) {
                return $q->order(['created' => 'DESC']);
            }])->all();
        } else {
            $allFeatures = $deviceFeatures->findByDeviceUuid($args['uuid'])->contain(['Units', 'DeviceStates' => function ($q) {
                return $q->order(['created' => 'DESC']);
            }])->all();
        }

        $data['count'] = $allFeatures->count();
        $data['features'] = $allFeatures->toArray();

        foreach ($allFeatures as $feature) {
            $feature->currentValue = $feature->device_states[0]->value;
            unset($feature->device_states);
        }
    }

    return $response->withData($data);
});

/**
 * @SWG\Get(
 *     path="/devices/{uuid}/features/{feature_id}/states",
 *     description="Returns all the states attached to a specific device feature",
 *     operationId="findDeviceFeatureStates",
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\Parameter(
 *          name="feature_id",
 *          in="path",
 *          description="ID of a device feature to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *     @SWG\Parameter(
 *          name="limit",
 *          in="query",
 *          description="Set the number of displayed states (10 by default)",
 *          required=false,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="List of features response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No feature to show",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 *
 * @SWG\Get(
 *     path="/devices/{uuid}/features/{feature_id}/states/{state_id}",
 *     description="Retrieve a specific state attached to a device feature",
 *     operationId="findDeviceFeatureStatesById",
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Parameter(
 *          name="feature_id",
 *          in="path",
 *          description="ID of a device feature to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *     @SWG\Parameter(
 *          name="state_id",
 *          in="path",
 *          description="ID of a state to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *      @SWG\Parameter(
 *          name="logical",
 *          in="query",
 *          description="Search a device feature by the internal ID of a feature",
 *          required=false,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="Device response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="Device not found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
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

/**
 * @SWG\Get(
 *     path="/units",
 *     description="Returns all the units from the system",
 *     operationId="findUnits",
 *     tags={"Units"},
 *     @SWG\Response(
 *          response=200,
 *          description="List of units response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No unit to show",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 *
 * @SWG\Get(
 *     path="/units/{id}",
 *     description="Returns the unit with the specified ID",
 *     operationId="findUnitById",
 *     tags={"Units"},
 *     @SWG\Parameter(
 *          name="id",
 *          in="path",
 *          description="ID of a unit to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=200,
 *          description="Unit response"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="Unit not found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
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

/**
 * @SWG\Put(
 *     path="/devices/{uuid}/features/{feature_id}/states",
 *     description="Update the state of a specific feature of a connected device. Note that a feature with sensor type cannot be updated by an other device.",
 *     operationId="updateDeviceFeatureState",
 *     tags={"s"},
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *      @SWG\Parameter(
 *          name="feature_id",
 *          in="path",
 *          description="ID of a device feature to fetch",
 *          required=true,
 *          type="integer"
 *      ),
 *      @SWG\Parameter(
 *          name="value",
 *          in="query",
 *          description="Value of the new feature state",
 *          required=true,
 *          type="integer"
 *      ),
 *     @SWG\Parameter(
 *          name="logical",
 *          in="query",
 *          description="Search a device feature by the internal ID of a feature",
 *          required=false,
 *          type="integer"
 *      ),
 *     @SWG\Response(
 *          response=201,
 *          description="Device state successfully updated"
 *      ),
 *     @SWG\Response(
 *          response=400,
 *          description="The value passed is not correct",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      ),
 *     @SWG\Response(
 *          response="default",
 *          description="Unexpected error",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
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
    $newState->value = $params['value'];
    $newState->device_feature_id = $feature->id;

    if (!$deviceStates->save($newState)) {
        throw new Exception(null, ErrorHandler::STATUS_INTERNAL_SERVER_ERROR);
    }

    $data = $newState->toArray();
    return $response->withData($data)->withStatus(ErrorHandler::STATUS_CREATED);
});

/**
 * @SWG\Put(
 *     path="/devices/{uuid}/states/reset",
 *     description="Reset the state of a specific connected device",
 *     operationId="resetDeviceFeatureState",
 *     tags={"Devices"},
 *     @SWG\Parameter(
 *          name="uuid",
 *          in="path",
 *          description="The unique identifier of a device to fetch",
 *          required=true,
 *          type="string"
 *      ),
 *     @SWG\Response(
 *          response=201,
 *          description="Device state successfully updated"
 *      ),
 *     @SWG\Response(
 *          response=404,
 *          description="No updatable feature found",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      ),
 *     @SWG\Response(
 *          response="default",
 *          description="Unexpected error",
 *          @SWG\Schema(
 *              ref="#/definitions/ErrorModel"
 *          )
 *      )
 * )
 */
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