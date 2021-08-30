<?php

use yii\db\Connection;

return [
    'id' => 'wpn-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => Connection::class,
            'dsn' => 'mysql:host=localhost;dbname=wpn_test_db;',
            'username' => 'root',
            'password' => 'password',
            'enableSchemaCache' => true,
            'charset' => 'latin1',
        ],
    ]
];