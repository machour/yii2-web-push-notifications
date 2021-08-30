<?php

namespace machour\yii2\wpn\components;

use machour\yii2\wpn\exceptions\InvalidApplication;
use machour\yii2\wpn\models\WpnCampaign;
use machour\yii2\wpn\models\WpnSubscription;
use Minishlink\WebPush\WebPush;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @see https://autopush.readthedocs.io/en/latest/http.html#error-codes
 */
class Pusher extends BaseObject
{
    private $wp;

    public function __construct(WebPush $wp, $config = [])
    {
        $this->wp = $wp;
        parent::__construct($config);
    }

    public function test()
    {
        return $this->wp;
    }

    /**
     * @throws \yii\db\Exception
     * @throws \ErrorException
     */
    public function sendPush(WpnCampaign $campaign)
    {
        if (!$campaign->app->enabled) {
            throw new InvalidApplication();
        }

        $auth = [
            'VAPID' => [
                'subject' => $campaign->app->subject,
                'publicKey' => $campaign->app->public_key,
                'privateKey' => $campaign->app->private_key,
            ],
        ];

        $payload = Json::encode($campaign->options);

        /** @var WpnSubscription[] $subscriptions */
        $subscriptions = ArrayHelper::map(WpnSubscription::find()->where(['subscribed' => true])->all(), 'endpoint', 'self');

        foreach ($subscriptions as $subscription) {
            try {
                $this->wp->queueNotification($subscription, $payload, [], $auth);
            } catch (\Exception $e) {
                $subscription->last_error = $e->getMessage();
                $subscription->save();
            }
        }

        $unsubscribedIds = [];

        $reports = [];

        foreach ($this->wp->flush() as $report) {
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