<?php

return [
    [
        'id' => 1,
        'endpoint' => 'endpoint-subscribed-1',
        'auth' => 'auth-1',
        'public_key' => 'public-1',
        'content_encoding' => 'encoding-1',
        'subscribed' => 1,
        'test_user' => 0,
        'app_id' => 1,
        'ip' => '127.0.0.1',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 2,
        'endpoint' => 'endpoint-subscribed-and-test-2',
        'auth' => 'auth-2',
        'public_key' => 'public-2',
        'content_encoding' => 'encoding-2',
        'subscribed' => 1,
        'test_user' => 1,
        'app_id' => 1,
        'ip' => '127.0.0.1',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 3,
        'endpoint' => 'endpoint-not-subscribed-3',
        'auth' => 'auth-3',
        'public_key' => 'public-3',
        'content_encoding' => 'encoding-3',
        'subscribed' => 0,
        'test_user' => 0,
        'app_id' => 1,
        'ip' => '127.0.0.1',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],

];