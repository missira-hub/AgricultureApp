<?php

return [

    'default' => env('BROADCAST_DRIVER', 'null'),

    'connections' => [
'pusher' => [
    'driver' => 'pusher',
    'key' => env('06854e67a611b7a83a0c'),
    'secret' => env('a272cf9f71ca74ba5244'),
    'app_id' => env('2017310'),
    'options' => [
        'cluster' => env('eu'),
        'useTLS' => true,
    
],

],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    

];
