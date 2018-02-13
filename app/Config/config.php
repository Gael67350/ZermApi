<?php

return [

    'app' => [
        'debug' => true,
        'timezone' => 'Europe/Paris'
    ],

    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'schema' => 'my_api'
    ],

    'auth' => [
        'public_key' => 'jwt_public.pem',
        'private_key' => 'jwt_private.pem'
    ],

];