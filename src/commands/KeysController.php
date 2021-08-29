<?php

namespace machour\yii2\wpn\commands;

use Minishlink\WebPush\VAPID;
use yii\console\Controller;

class KeysController extends Controller
{
    public function actionIndex()
    {
        $keys = VAPID::createVapidKeys();

        var_dump($keys);
    }
}