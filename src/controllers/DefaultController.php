<?php

namespace machour\yii2\wpn\controllers;

use DeviceDetector\DeviceDetector;
use machour\yii2\wpn\components\Pusher;
use machour\yii2\wpn\exceptions\SubscriptionNotFound;
use machour\yii2\wpn\models\WpnCampaign;
use machour\yii2\wpn\models\WpnSubscription;
use machour\yii2\wpn\models\WpnReport;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use Yii;
use yii\base\InvalidArgumentException;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        if ($action->id == 'report') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

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
     * @throws SubscriptionNotFound
     * @throws Exception
     */
    public function actionSync($appId): \yii\web\Response
    {
        $request = Yii::$app->request;
        $data = Json::decode($request->rawBody);

        if (!isset($data['endpoint'])) {
            return $this->asJson([
                'success' => false,
                'message' => 'Not a subscription',
            ]);
        }

        switch ($request->method) {
            case 'POST':

                $subscription = new WpnSubscription([
                    'app_id' => $appId,
                    'endpoint' => $data['endpoint'],
                    'auth' => $data['authToken'],
                    'public_key' => $data['publicKey'],
                    'content_encoding' => $data['contentEncoding'],
                    'subscribed' => true,
                    'yii_user_id' => Yii::$app->user->id,
                    'ua' => $request->userAgent,
                    'ip' => $request->remoteIP,
                    'test_user' => false,
                    'last_seen' => new Expression('NOW()'),
                ]);

                $dd = new DeviceDetector($subscription->ua);
                $dd->parse();
                $subscription->os = $dd->getOs('name');
                $subscription->browser = $dd->getClient('name');

                if ($subscription->save()) {
                    return $this->asJson(['success' => true, 'user_id' => $subscription->id]);
                } else {
                    return $this->asJson(['success' => false, 'message' => var_export($subscription->errors, 1)]);
                }

            case 'PUT':
                $subscription = WpnSubscription::findOne(['endpoint' => $data['endpoint']]);
                if ($subscription) {
                    $subscription->setAttributes([
                        'subscribed' => true,
                        'auth' => $data['authToken'],
                        'public_key' => $data['publicKey'],
                        'content_encoding' => $data['contentEncoding'],
                        'ua' => $request->userAgent,
                        'ip' => $request->remoteIP,
                        'last_seen' => new Expression('NOW()'),
                    ]);
                    if (!Yii::$app->user->isGuest) {
                        $subscription->yii_user_id = Yii::$app->user->id;
                    }

                    if ($subscription->save()) {
                        return $this->asJson(['success' => true, 'user_id' => $subscription->id]);
                    }

                    return $this->asJson(['success' => false, 'message' => var_export($subscription->errors, 1)]);
                }

                throw new SubscriptionNotFound();

            case 'DELETE':
                $subscription = WpnSubscription::findOne(['endpoint' => $data['endpoint'], 'subscribed' => true]);
                if ($subscription) {
                    $subscription->subscribed = false;
                    $subscription->reason = 'user request';
                    $subscription->save();
                    return $this->asJson(['success' => true, 'user_id' => $subscription->id]);
                }

                return $this->asJson(['success' => false, 'message' => 'Subscription not found']);
        }

    }

    public function actionReport(): \yii\web\Response
    {
        $request = Yii::$app->request;
        $data = Json::decode($request->rawBody);

        $campaign_id = $data['campaignId'];
        $endpoint = $data['endpoint'];
        $action = $data['action'];

        $campaign = WpnCampaign::findOne($campaign_id);
        if (!$campaign) {
            throw new InvalidArgumentException("Campaign not found");
        }

        $subscription = WpnSubscription::findOne(['endpoint' => $endpoint, 'app_id' => $campaign->app_id]);
        if (!$subscription) {
            throw new InvalidArgumentException("Subscription not found");
        }

        $sp = WpnReport::findOne(['campaign_id' => $campaign_id, 'subscription_id' => $subscription->id]);
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
            $campaign_id, $endpoint, $action, $sp->save()
        ]);
    }

    public function actionServiceWorker(): string
    {
        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->add('Content-Type', 'application/javascript');

        return file_get_contents(__DIR__ . '/../assets/sw.js');
    }

    public function actionPush($id)
    {
        $campaign = WpnCampaign::findOne($id);
        if (!$campaign) {
            throw new NotFoundHttpException("Unknown Campaign");
        }

        /** @var Pusher $pusher */
        $pusher = $this->module->get('pusher');

        $pusher->sendPush($campaign);
    }
}