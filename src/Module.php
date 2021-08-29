<?php

namespace common\modules\wpn;

use yii\base\InvalidConfigException;

class Module extends \yii\base\Module
{
    /**
     * @var string The app name, usefull when using several instances of this module
     */
    public $app = 'default';

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
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->privateKey) {
            throw new InvalidConfigException("The private key must be set");
        }
        if (!$this->publicKey) {
            throw new InvalidConfigException("The public key must be set");
        }
        if (!$this->subject) {
            throw new InvalidConfigException("The subjet must be set");
        }

        parent::init();
    }
}