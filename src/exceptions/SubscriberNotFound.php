<?php

namespace common\modules\wpn\exceptions;

use yii\base\Exception;

class SubscriberNotFound extends Exception
{
    public $message = 'Subscriber not found';
}