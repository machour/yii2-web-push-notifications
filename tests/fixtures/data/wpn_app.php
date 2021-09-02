<?php

return [
    [
        'id' => 1,
        'name' => 'Valid App',
        'host' => 'localhost',
        'subject' => 'mailto:contact@example.com',
        'public_key' => 'BPNOgWF76BVzSYpRWDWV3SVc9wvZgfvImhhSQNLYGRs_Zfy5rEXb5NITVoUzbEoe9E85oO2830ftuZfwjmHr0FE',
        'private_key' => 'KRbzYHPtD54HPyXP6bkqavNUiTGKls8C0rLyAk7fCoU',
        'enabled' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 2,
        'name' => 'Disabled App',
        'host' => 'localhost2',
        'subject' => 'mailto:contact@example.com',
        'public_key' => 'BPNOgWF76BVzSYpRWDWV3SVc9wvZgfvImhhSQNLYGRs_Zfy5rEXb5NITVoUzbEoe9E85oO2830ftuZfwjmHr0FE',
        'private_key' => 'KRbzYHPtD54HPyXP6bkqavNUiTGKls8C0rLyAk7fCoU',
        'enabled' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
    [
        'id' => 3,
        'name' => 'Valid App with icon',
        'host' => 'localhost3',
        'subject' => 'mailto:contact@example.com',
        'public_key' => 'BPNOgWF76BVzSYpRWDWV3SVc9wvZgfvImhhSQNLYGRs_Zfy5rEXb5NITVoUzbEoe9E85oO2830ftuZfwjmHr0FE',
        'private_key' => 'KRbzYHPtD54HPyXP6bkqavNUiTGKls8C0rLyAk7fCoU',
        'icon' => 'https://www.yiiframework.com/image/design/logo/yii3_sign.png',
        'enabled' => false,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ],
];