<?php

namespace machour\yii2\wpn\widgets;

use machour\yii2\wpn\models\WpnApp;
use yii\base\InvalidConfigException;
use yii\base\Widget;

class SubscriptionWidget extends Widget
{
    /** @var WpnApp */
    public $app;

    /** @var string */
    public $shouldMigrate;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->app || !is_a($this->app, WpnApp::class)) {
            throw new InvalidConfigException('A valid WpnApp must be provided in the $app property');
        }
        parent::init();
    }

    public function run(): string
    {
        if (!$this->app->enabled) {
            return '';
        }

        return $this->render('subscription', [
            'app' => $this->app,
            'shouldMigrate' => $this->shouldMigrate,
        ]);
    }
}