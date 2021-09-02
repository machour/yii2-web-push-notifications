<?php

return [
    [
        'id' => 1,
        'app_id' => 1,
        'title' => 'Push on valid application',
        'tag' => 'push-1',
        'body' => 'My push body',
        'created_at' => date('Y-m-d H:i:s'),
        'scheduled_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 2,
        'app_id' => 2,
        'title' => 'Push on invalid application',
        'tag' => 'push-2',
        'body' => 'My push body',
        'created_at' => date('Y-m-d H:i:s'),
        'scheduled_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 3,
        'app_id' => 3,
        'title' => 'Push on application with icon',
        'tag' => 'push-3',
        'body' => 'My push body',
        'created_at' => date('Y-m-d H:i:s'),
        'scheduled_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 4,
        'app_id' => 1,
        'title' => 'Push for test users only',
        'tag' => 'push-test',
        'body' => 'My test push body',
        'created_at' => date('Y-m-d H:i:s'),
        'scheduled_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
];