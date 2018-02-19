<?php

return [

    'app' => [
        'debug' => true,
        'timezone' => 'Europe/Paris'
    ],

    'database' => [
        'driver' => 'Cake\Database\Driver\Mysql',
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'schema' => 'zermbox'
    ],

    'auth' => [
        'public_key' => 'jwtrsa_zermapi_cert.pem',
        'private_key' => 'jwtrsa_zermapi_private.pem'
    ],

];