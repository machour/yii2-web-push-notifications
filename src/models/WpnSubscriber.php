<?php

namespace machour\yii2\wpn\models;

use Minishlink\WebPush\SubscriptionInterface;
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
 * @property int $yii_user_id
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
class WpnSubscriber extends ActiveRecord implements SubscriptionInterface
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
            [['yii_user_id'], 'integer'],
        ];
    }

    public function getWpnSubscriberPushes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnSubscriberPush::class, ['wpn_subscriber_id' => 'id']);
    }

    public function getWpnPushes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnPush::class, ['id' => 'wpn_push_id'])->via('wpnSubscriberPushes');
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getPublicKey(): ?string
    {
        return $this->p256dh;
    }

    public function getAuthToken(): ?string
    {
        return $this->auth;
    }

    public function getContentEncoding(): ?string
    {
        return $this->content_encoding;
    }
}
