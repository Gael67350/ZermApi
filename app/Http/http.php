<?php

$container = $app->getContainer();

$container['response'] = function ($container) {
    $response = new \App\Http\Response();
    return $response->withProtocolVersion($container->get('settings')['httpVersion']);
};