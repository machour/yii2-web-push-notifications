<?php

use yii\db\Connection;

return [
    'id' => 'wpn-tests',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['wpn'],
    'components' => [
        'db' => [
            'class' => Connection::class,
            'dsn' => 'mysql:host=127.0.0.1;dbname=wpn_test_db;',
            'username' => 'root',
            'password' => 'password',
            'enableSchemaCache' => true,
            'charset' => 'latin1',
        ],
    ],
    'modules' => [
        'wpn' => [
            'class' => \machour\yii2\wpn\Module::class,
            'components' => [
                'pusher' => [
                    'class' => \machour\yii2\wpn\components\Pusher::class,
                ]
            ]
        ]
    ]
];