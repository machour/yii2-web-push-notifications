<?php

namespace machour\yii2\wpn\exceptions;

use yii\base\Exception;

class InvalidApplication extends Exception
{
    public $message = 'Application disabled';
}