<?php

namespace machour\yii2\wpn\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wpn_report".
 *
 * @property int $id
 * @property int $campaign_id
 * @property int $subscription_id
 * @property string $sent_at
 * @property int $received
 * @property int $viewed
 * @property int $clicked
 * @property int $dismissed
 * @property int $unsubscribed
 *
 * @property WpnCampaign $campaign
 * @property WpnSubscription $subscription
 */
class WpnReport extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%wpn_report}}';
    }

    public function rules(): array
    {
        return [
            [['campaign_id', 'subscription_id', 'sent_at'], 'required'],
            [['campaign_id', 'subscription_id'], 'integer'],
            [['sent_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['received', 'viewed', 'clicked', 'dismissed', 'unsubscribed'], 'boolean'],
            [['subscription_id', 'campaign_id'], 'unique', 'targetAttribute' => ['subscription_id', 'campaign_id']],
            [['campaign_id'], 'exist', 'skipOnError' => true, 'targetClass' => WpnCampaign::class, 'targetAttribute' => ['campaign_id' => 'id']],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => WpnSubscription::class, 'targetAttribute' => ['subscription_id' => 'id']],
        ];
    }

    public function getCampaign(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WpnCampaign::class, ['id' => 'campaign_id']);
    }

    public function getSubscription(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WpnSubscription::class, ['id' => 'subscription_id']);
    }
}
