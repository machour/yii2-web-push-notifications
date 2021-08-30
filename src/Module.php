<?php

namespace machour\yii2\wpn;

use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var string the namespace that controller classes are in.
     */
    public $controllerNamespace = 'machour\yii2\wpn\controllers';

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'machour\yii2\wpn\commands';
        }
    }

}