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

    /**
     * @throws \yii\db\Exception
     * @throws \ErrorException
     * @throws InvalidApplication
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



        $query = WpnSubscription::find()
                ->where(['subscribed' => true, 'app_id' => $campaign->app_id])
                ->orderBy(['last_seen' => SORT_DESC]);

        if ($campaign->test_only) {
            $query->andWhere(['test_user' => 1]);
        }

        /** @var WpnSubscription[] $subscriptions */
        $subscriptions = ArrayHelper::index($query->all(), 'endpoint');

        foreach ($subscriptions as $subscription) {
            try {
                $options = $campaign->options;
                $options['data']['endpoint'] = $subscription->endpoint;

                $this->wp->queueNotification($subscription, Json::encode($options), [], $auth);
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
                $response = $report->getResponse();
                if ($report->isSubscriptionExpired()) {
                    $unsubscribedIds[] = $subscription->id;
                } else if ($response->getStatusCode() === 301) {
                    $newEndpoint = $response->getHeaderLine('Location');
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
            WpnSubscription::updateAll([
                'subscribed' => false,
                'reason' => 'last push failed'
            ], ['id' => $unsubscribedIds]);
        }

        if (count($reports)) {
            Yii::$app->db->createCommand()
                ->batchInsert(
                    '{{%wpn_report}}', array_keys($reports[0]), $reports)
                ->execute();
        }
    }
}