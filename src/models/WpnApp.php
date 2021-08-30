<?php

namespace machour\yii2\wpn\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "wpn_app".
 *
 * @property int $id
 * @property string $name
 * @property string $host
 * @property string $private_key
 * @property string $public_key
 * @property int $enabled
 * @property string $created_at
 * @property string $updated_at
 *
 * @property WpnCampaign[] $campaigns
 * @property WpnSubscription[] $subscriptions
 */
class WpnApp extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%wpn_app}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['name', 'host', 'enabled', 'public_key', 'private_key'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['host'], 'string', 'max' => 180],
            [['private_key'], 'string', 'max' => 50],
            [['public_key'], 'string', 'max' => 100],
            [['enabled'], 'boolean'],
            [['host'], 'unique'],
        ];
    }

    public function getCampaigns(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnCampaign::class, ['app_id' => 'id']);
    }

    public function getSubscriptions(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnSubscription::class, ['app_id' => 'id']);
    }
}
