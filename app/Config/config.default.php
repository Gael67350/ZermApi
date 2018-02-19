<?php

return [

    'app' => [
        'debug' => true,
        'timezone' => 'UTC'
    ],

    'database' => [
        'driver' => 'Cake\Database\Driver\Mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'schema' => 'zermbox'
    ],

    'auth' => [
        'public_key' => 'jwt_public.pem',
        'private_key' => 'jwt_private.pem'
    ],

];