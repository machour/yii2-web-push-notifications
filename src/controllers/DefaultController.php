<?php

namespace common\modules\wpn\controllers;

use common\modules\wpn\exceptions\SubscriberNotFound;
use common\modules\wpn\helpers\WebPushNotifications;
use common\modules\wpn\models\WpnPush;
use common\modules\wpn\models\WpnSubscriber;
use common\modules\wpn\models\WpnSubscriberPush;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Expression;

class DefaultController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sync' => ['post', 'put', 'delete'],
                    'report' => ['post'],
                    'push' => ['get'],
                ],
            ],
        ];
    }

    /**
     * @throws SubscriberNotFound
     * @throws Exception
     */
    public function actionSync(): \yii\web\Response
    {
        $request = Yii::$app->request;
        $subscription = json_decode($request->rawBody, true);

        if (!isset($subscription['endpoint'])) {
            return $this->asJson([
                'success' => false,
                'message' => 'Not a subscription',
            ]);
        }

        switch ($request->method) {
            case 'POST':
                $subscriber = new WpnSubscriber([
                    'app' => $this->module->app,
                    'endpoint' => $subscription['endpoint'],
                    'auth' => $subscription['authToken'],
                    'p256dh' => $subscription['publicKey'],
                    'content_encoding' => $subscription['contentEncoding'],
                    'subscribed' => true,
                    'ua' => $request->userAgent,
                    'ip' => $request->remoteIP,
                    'test_user' => false,
                    'last_seen' => new Expression('NOW()'),
                ]);

                if ($subscriber->save()) {
                    return $this->asJson(['success' => true, 'user_id' => $subscriber->id]);
                } else {
                    return $this->asJson(['success' => false, 'message' => var_export($subscriber->errors, 1)]);
                }

            case 'PUT':
                $subscriber = WpnSubscriber::findOne(['endpoint' => $subscription['endpoint']]);
                if ($subscriber) {
                    $subscriber->setAttributes([
                        'subscribed' => true,
                        'auth' => $subscription['authToken'],
                        'p256dh' => $subscription['publicKey'],
                        'content_encoding' => $subscription['contentEncoding'],
                        'ua' => $request->userAgent,
                        'ip' => $request->remoteIP,
                        'last_seen' => new Expression('NOW()'),
                    ]);

                    if ($subscriber->save()) {
                        return $this->asJson(['success' => true, 'user_id' => $subscriber->id]);
                    }

                    return $this->asJson(['success' => false, 'message' => var_export($subscriber->errors, 1)]);
                }

                throw new SubscriberNotFound();


            case 'DELETE':
                $subscriber = WpnSubscriber::findOne(['endpoint' => $subscription['endpoint'], 'subscribed' => true]);
                if ($subscriber) {
                    $subscriber->subscribed = false;
                    $subscriber->reason = 'user request';
                    $subscriber->save();
                    return $this->asJson(['success' => true, 'user_id' => $subscriber->id]);
                }
                throw new SubscriberNotFound();
        }

    }

    public function actionReport(): \yii\web\Response
    {
        $push_id = Yii::$app->request->post('pushId', false);
        $endpoint = Yii::$app->request->post('endpoint', false);
        $action = Yii::$app->request->post('action', false);

        $subscriber = WpnSubscriber::findOne(['endpoint' => $endpoint]);
        if (!$subscriber) {
            throw new InvalidArgumentException("Subscriber not found");
        }

        $sp = WpnSubscriberPush::findOne(['wpn_push_id' => $push_id, 'wpn_subscriber_id' => $subscriber->id]);
        if (!$sp) {
            throw new InvalidArgumentException("Push not sent to this user");
        }

        switch ($action) {
            case 'dismiss':
                $sp->dismissed = true;
                break;

            case 'click':
                $sp->clicked = true;
                break;
        }

        return $this->asJson([
            $push_id, $endpoint, $action, $sp->save()
        ]);
    }

    private function actionPush($id)
    {
        WebPushNotifications::sendPush(WpnPush::findOne($id));
    }
}