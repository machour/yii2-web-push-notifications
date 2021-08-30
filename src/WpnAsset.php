<?php

namespace machour\yii2\wpn;

use yii\web\AssetBundle;

/**
 * Wpn asset bundle
 */
class WpnAsset extends AssetBundle
{
    /**
     * {@inheritdoc}
     */
    public $sourcePath = '@vendor/machour/yii2-web-push-notifications/src/assets';
    /**
     * {@inheritdoc}
     */
    public $js = [
        'web-push.js',
    ];
}
