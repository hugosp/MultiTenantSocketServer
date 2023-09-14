<?php

require_once 'vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Hewgo\MessageBroker;

$broker = new MessageBroker();
$interval = 5; // seconds

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $broker
        )
    ),
    8080
);

$count = 0;

$server->loop->addPeriodicTimer($interval, function () use ($broker, &$count) {


    // fetch data from database with uids and send thier data to clients
    $mock_result = [
        'all' => [
            'public' => [
                [
                    'type' => 'timer',
                    'data' => $count,
                ],
            ],
            'internal' => [
                [
                    'type' => 'queue_status',
                    'data' => [
                        'small' => true,
                        'medium' => false,
                        'big' => true,
                    ]
                ]
            ]
        ],
        'company' => [
            710 => [
                [
                    'type' => 'message',
                    'data' => [
                        'message' => 'Hello  company 710'
                    ]
                ],
            ],
            553 => [
                [
                    'type' => 'message',
                    'data' => [
                        'message' => 'Hello company 553'
                    ]
                ],
            ],
        ],
        'user' => [
            194 => [
                [
                    'type' => 'message',
                    'data' => [
                        'message' => 'Hello private user 194'
                    ]
                ],
            ]
        ],
    ];

    foreach ($mock_result as $type => $items) {
        foreach ($items as $uid => $array) {
            foreach ($array as $data) {
                $broker->send($type, $uid, $data);
            }
        }
    }

    $count++;
});


$server->run();
