<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Web Push Notifications for Yii 2</h1>
    <br>
</p>

An extension for implementing Web Push Notifications on your website in a breeze.

[![Latest Stable Version](https://poser.pugx.org/machour/yii2-web-push-notifications/v/stable.png)](https://packagist.org/packages/machour/yii2-web-push-notifications)
[![Total Downloads](https://poser.pugx.org/machour/yii2-web-push-notifications/downloads.png)](https://packagist.org/packages/machour/yii2-web-push-notifications)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
php composer.phar require --prefer-dist machour/yii2-web-push-notification
```

Configuration
-------------

### DB


This module use the following tables:

| Name                    | Role                                                                 |
|-------------------------|----------------------------------------------------------------------|
| `{{%wpn_app}}`          | Represents a Web Push application                                    |
| `{{%wpn_subscription}}` | Represents a Web Push subscriber                                     |
| `{{%wpn_campaign}}`     | Represents a Web Push campaign (ie, a push you've scheduled or sent) |
| `{{%wpn_report}}`       | Links a subscriber to a push (received ? errored ? ..)               |

Use the following migration to create them:
```bash
 ./yii migrate --migrationPath=vendor/machour/yii2-web-push-notifications/src/migrations/
```

### Web

Add this module to your `\yii\web\Application` config file :

```php
return [
   // ...
    'modules' => [
        'wpn' => [
            'class' => 'machour\yii2\wpn\Module',
        ],
        // ...
    ],
];
```

### Console

Add this module to your `\yii\console\Application` config file :

```php
return [
    // ...
    'bootstrap' => [..., 'wpn'],
    // ...
        'modules' => [
        'wpn' => [
            'class' => 'machour\yii2\wpn\Module',
        ],
        // ...
    ],
]
```

You can now register a new application using the `./yii wpn/app/create` console command.
Arguments are as follow:

```
USAGE

yii wpn/app/create <name> <host> <subject> [...options...]

- name (required): string
  The application name

- host (required): string
  The hostname where the application will be deployed

- subject (required): string
  The contact for the application. Needs to be a URL or a mailto: URL.
  This provides a point of contact in case the push service needs to contact you
```
