<?php

namespace machour\yii2\wpn\helpers;

use machour\yii2\wpn\models\WpnCampaign;
use machour\yii2\wpn\models\WpnSubscription;
use machour\yii2\wpn\Module;
use Minishlink\WebPush\WebPush;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @see https://autopush.readthedocs.io/en/latest/http.html#error-codes
 */
class WebPushNotifications
{
    /**
     * @throws \yii\db\Exception
     * @throws \ErrorException
     */
    public static function sendPush(WpnCampaign $campaign)
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('wpn');
        $auth = [
            'VAPID' => [
                'subject' => $module->subject,
                'publicKey' => $module->publicKey,
                'privateKey' => $module->privateKey,
            ],
        ];

        $webPush = new WebPush($auth);

        $payload = Json::encode($campaign->options);

        /** @var WpnSubscription[] $subscriptions */
        $subscriptions = ArrayHelper::map(WpnSubscription::find()->where(['subscribed' => true])->all(), 'endpoint', 'self');

        foreach ($subscriptions as $subscription) {
            try {
                $webPush->queueNotification($subscription, $payload);
            } catch (\Exception $e) {
                $subscription->last_error = $e->getMessage();
                $subscription->save();
            }
        }

        $unsubscribedIds = [];

        $reports = [];

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $subscription = $subscriptions[$endpoint];
            $spParams = [
                'subscription_id' => $subscription->id,
                'campaign_id' => $campaign->id,
                'sent_at' => date('Y-m-d H:i:s'),
                'received' => true,
            ];

            if (!$report->isSuccess()) {
                $spParams['received'] = false;
                if ($report->isSubscriptionExpired()) {
                    $json = Json::decode($report->getResponse()->getBody()->getContents());
                    echo "<pre>";
                    var_dump($json);
                    echo "</pre>";

                    $unsubscribedIds[] = $subscription->id;

                } else if ($report->getResponse()->getStatusCode() === 301) {
                    $newEndpoint = $report->getResponse()->getHeaderLine('Location');
                    if ($newEndpoint) {
                        $subscription->endpoint = $newEndpoint;
                        $subscription->save();
                    } else {
                        $unsubscribedIds[] = $subscription->id;
                    }
                }
            }

            $reports[] = $spParams;

        }

        if (count($unsubscribedIds)) {
            WpnSubscription::updateAll(['subscribed' => false, 'reason' => 'last push failed'], ['id' => $unsubscribedIds]);
        }

        if (count($reports)) {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    '{{%wpn_report}}', array_keys($reports[0]), $reports)
                ->execute();
        }
    }
}