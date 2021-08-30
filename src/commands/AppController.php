<?php

namespace machour\yii2\wpn\commands;

use machour\yii2\wpn\models\WpnApp;
use Minishlink\WebPush\VAPID;
use yii\console\Controller;
use yii\console\widgets\Table;
use yii\helpers\Console;

class AppController extends Controller
{
    public function actionIndex()
    {
        $apps = WpnApp::find()->all();

        $rows = [];

        foreach ($apps as $app) {
            $rows[] = [$app->id, $app->name, $app->host, $app->subject, $app->enabled ? $this->ansiFormat('✓', Console::FG_GREEN)  : $this->ansiFormat('x', Console::FG_RED)];
        }

        echo Table::widget([
            'headers' => ['ID', 'Name', 'Host', 'Subject', 'Enabled'],
            'rows' => $rows
        ]);
    }

    /**
     * @param string $name The application name
     * @param string $host The hostname where the application will be deployed
     * @param string $subject The contact for the application
     */
    public function actionCreate($name, $host, $subject)
    {
        try {
            $keys = VAPID::createVapidKeys();
            $app = new WpnApp([
                'name' => $name,
                'host' => $host,
                'subject' => $subject,
                'private_key' => $keys['privateKey'],
                'public_key' => $keys['publicKey'],
                'enabled' => true,
            ]);

            if ($app->save()) {
                $this->stdout("Application #{$app->id} created and enabled.\n", Console::FG_GREEN);
            } else {
                $this->stdout("Application could not be created. Please fix the following errors:\n\n", Console::FG_RED);
                foreach ($app->errors as $attribute => $errors) {
                    echo ' - ' . $this->ansiFormat($attribute, Console::FG_CYAN) . ': ' . implode('; ', $errors) . "\n";
                }
            }
        } catch (\ErrorException $e) {
            $this->stdout("Could not generate VapId keys: {$e->getMessage()}\n", Console::FG_RED);
        }

    }

}