<?php

namespace machour\yii2\wpn\exceptions;

use yii\base\Exception;

class SubscriptionNotFound extends Exception
{
    public $message = 'Subscription not found';
}