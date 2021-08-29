<?php

namespace machour\yii2\wpn\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "wpn_subscriber".
 *
 * @property int $id
 * @property string $endpoint
 * @property string $auth
 * @property string $p256dh
 * @property string $content_encoding
 * @property int $subscribed
 * @property int $test_user
 * @property string $app
 * @property string $ua
 * @property string $ip
 * @property string $os
 * @property string $browser
 * @property string $last_seen
 * @property string $last_error
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 *
 * @property WpnSubscriberPush[] $wpnSubscriberPushes
 * @property WpnPush[] $wpnPushes
 */
class WpnSubscriber extends ActiveRecord
{

    public static function tableName(): string
    {
        return 'wpn_subscriber';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'last_seen', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => ['updated_at', 'last_seen'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['endpoint', 'auth', 'p256dh', 'content_encoding', 'subscribed', 'app', 'ip'], 'required'],
            [['endpoint', 'auth', 'p256dh', 'content_encoding', 'app', 'ua', 'ip', 'os', 'browser', 'last_error', 'reason'], 'string', 'max' => 255],
            [['subscribed', 'test_user'], 'boolean'],
            [['endpoint'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('models', 'ID'),
            'endpoint' => Yii::t('models', 'Endpoint'),
            'auth' => Yii::t('models', 'Auth'),
            'p256dh' => Yii::t('models', 'P256dh'),
            'content_encoding' => Yii::t('models', 'Content Encoding'),
            'subscribed' => Yii::t('models', 'Subscribed'),
            'test_user' => Yii::t('models', 'Test User'),
            'app' => Yii::t('models', 'App'),
            'ua' => Yii::t('models', 'Ua'),
            'ip' => Yii::t('models', 'Ip'),
            'os' => Yii::t('models', 'Os'),
            'browser' => Yii::t('models', 'Browser'),
            'last_seen' => Yii::t('models', 'Last Seen'),
            'last_error' => Yii::t('models', 'Last Error'),
            'reason' => Yii::t('models', 'Reason'),
            'created_at' => Yii::t('models', 'Created At'),
            'updated_at' => Yii::t('models', 'Updated At'),
            'self' => Yii::t('models', 'Wpn subscriber'),
            'wpnSubscriberPushes' => Yii::t('models', 'WpnSubscriberPushes'),
            'wpnPushes' => Yii::t('models', 'WpnPushes'),
        ];
    }

    public function getWpnSubscriberPushes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnSubscriberPush::class, ['wpn_subscriber_id' => 'id']);
    }

    public function getWpnPushes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnPush::class, ['id' => 'wpn_push_id'])->viaTable('wpn_subscriber_push', ['wpn_subscriber_id' => 'id']);
    }
}
