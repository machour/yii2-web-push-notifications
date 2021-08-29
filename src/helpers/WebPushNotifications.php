<?php

namespace machour\yii2\wpn\helpers;

use machour\yii2\wpn\models\WpnPush;
use machour\yii2\wpn\models\WpnSubscriber;
use machour\yii2\wpn\Module;
use Minishlink\WebPush\Subscription;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @see https://autopush.readthedocs.io/en/latest/http.html#error-codes
 */
class WebPushNotifications
{
    public static function sendPush(WpnPush $push)
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

        $webPush = new \Minishlink\WebPush\WebPush($auth);

        $payload = json_encode($push->options);

        /** @var WpnSubscriber[] $subscribers */
        $subscribers = ArrayHelper::map(WpnSubscriber::find()->where(['subscribed' => true])->all(), 'endpoint', 'self');

        foreach ($subscribers as $subscriber) {
            $subscription = Subscription::create([
                'endpoint' => $subscriber->endpoint,
                "keys" => [
                    'p256dh' => $subscriber->p256dh,
                    'auth' => $subscriber->auth,
                ],
                'contentEncoding' => $subscriber->content_encoding,
            ]);

            try {
                $webPush->queueNotification($subscription, $payload);
            } catch (\Exception $e) {
                $subscriber->last_error = $e->getMessage();
                $subscriber->save();
            }
        }

        $reports = $webPush->flush();

        $unsubscribedIds = [];

        $subscribersPushs = [];

        foreach ($reports as $idx => $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $subscriber = $subscribers[$endpoint];


            $spParams = [
                'wpn_subscriber_id' => $subscriber->id,
                'wpn_push_id' => $push->id,
                'sent_at' => date('Y-m-d H:i:s'),
                'received' => true,
            ];

            if (!$report->isSuccess()) {
                $spParams['received'] = false;
                if ($report->isSubscriptionExpired()) {
                    $json = json_decode($report->getResponse()->getBody()->getContents(), true);
                    echo "<pre>";
                    var_dump($json);
                    echo "</pre>";

                    $unsubscribedIds[] = $subscriber->id;

                } else if ($report->getResponse()->getStatusCode() === 301) {
                    $newEndpoint = $report->getResponse()->getHeaderLine('Location');
                    if ($newEndpoint) {
                        $subscriber->endpoint = $newEndpoint;
                        $subscriber->save();
                    } else {
                        $unsubscribedIds[] = $subscriber->id;
                    }
                }
            }

            $subscribersPushs[] = $spParams;

        }

        if (count($unsubscribedIds)) {
            WpnSubscriber::updateAll(['subscribed' => false, 'reason' => 'last push failed'], ['id' => $unsubscribedIds]);
        }

        if (count($subscribersPushs)) {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    '{{%wpn_subscriber_push}}', array_keys($subscribersPushs[0]), $subscribersPushs)
                ->execute();
        }
    }
}