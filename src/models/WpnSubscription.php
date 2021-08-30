<?php

namespace machour\yii2\wpn\models;

use Minishlink\WebPush\SubscriptionInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "wpn_subscription".
 *
 * @property int $id
 * @property string $endpoint
 * @property string $auth
 * @property string $public_key
 * @property string $content_encoding
 * @property int $subscribed
 * @property int $test_user
 * @property int $yii_user_id
 * @property int $app_id
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
 * @property WpnReport[] $reports
 * @property WpnCampaign[] $campaigns
 * @property WpnApp $app
 */
class WpnSubscription extends ActiveRecord implements SubscriptionInterface
{

    public static function tableName(): string
    {
        return '{{%wpn_subscription}}';
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
            [['endpoint', 'auth', 'public_key', 'content_encoding', 'subscribed', 'ip', 'app_id'], 'required'],
            [['yii_user_id', 'app_id'], 'integer'],
            [['endpoint', 'auth', 'public_key', 'content_encoding', 'ua', 'ip', 'os', 'browser', 'last_error', 'reason'], 'string', 'max' => 255],
            [['subscribed', 'test_user'], 'boolean'],
            [['endpoint'], 'unique'],
            [['app_id'], 'exist', 'skipOnError' => true, 'targetClass' => WpnApp::class, 'targetAttribute' => ['app_id' => 'id']],
        ];
    }

    public function getReports(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnReport::class, ['subscription_id' => 'id']);
    }

    public function getCampaigns(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnCampaign::class, ['id' => 'campaign_id'])->via('reports');
    }

    public function getApp(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WpnApp::class, ['id' => 'app_id']);
    }

    public function setEndpoint($value)
    {
        $this->endpoint = $value;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function getPublicKey(): ?string
    {
        return $this->public_key;
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
