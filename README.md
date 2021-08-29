<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Web Push Notifications for Yii 2</h1>
    <br>
</p>

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/machour/yii2-web-push-notifications/v/stable.png)](https://packagist.org/packages/machour/yii2-web-push-notifications)
[![Total Downloads](https://poser.pugx.org/machour/yii2-web-push-notifications/downloads.png)](https://packagist.org/packages/machour/yii2-web-push-notifications)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist machour/yii2-web-push-notification
```

or add

```
"machour/yii2-web-push-notification": "~1.0.0"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'modules' => [
        'debug' => [
            'class' => 'machour\yii2\wpn\Module',
        ],
        // ...
    ],
    ...
];
```