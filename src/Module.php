<?php

namespace machour\yii2\wpn;

use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\console\Application;
use Yii;

class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * @var string the namespace that controller classes are in.
     */
    public $controllerNamespace = 'machour\yii2\wpn\controllers';

    /**
     * @var string Your public key
     */
    public $publicKey;

    /**
     * @var string Your private key
     */
    public $privateKey;

    /**
     * @var string The push subject
     *
     * The subject needs to be a URL or a mailto: URL. This provides a point of contact in case the push service needs
     * to contact the message sender.
     */
    public $subject;


    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'machour\yii2\wpn\commands';
        }
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (Yii::$app instanceof \yii\web\Application) {
            if (!$this->privateKey) {
                throw new InvalidConfigException("The private key must be set");
            }
            if (!$this->publicKey) {
                throw new InvalidConfigException("The public key must be set");
            }
            if (!$this->subject) {
                throw new InvalidConfigException("The subjet must be set");
            }
        }

        parent::init();
    }
}