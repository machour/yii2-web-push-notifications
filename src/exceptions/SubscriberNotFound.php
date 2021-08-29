<?php

namespace machour\yii2\wpn\exceptions;

use yii\base\Exception;

class SubscriberNotFound extends Exception
{
    public $message = 'Subscriber not found';
}