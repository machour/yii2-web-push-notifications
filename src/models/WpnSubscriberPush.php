<?php

namespace machour\yii2\wpn\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "wpn_subscriber_push".
 *
 * @property int $id
 * @property int $wpn_push_id
 * @property int $wpn_subscriber_id
 * @property string $sent_at
 * @property int $received
 * @property int $viewed
 * @property int $clicked
 * @property int $dismissed
 * @property int $unsubscribed
 *
 * @property WpnPush $wpnPush
 * @property WpnSubscriber $wpnSubscriber
 */
class WpnSubscriberPush extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%wpn_subscriber_push}}';
    }

    public function rules(): array
    {
        return [
            [['wpn_push_id', 'wpn_subscriber_id', 'sent_at'], 'required'],
            [['wpn_push_id', 'wpn_subscriber_id'], 'integer'],
            [['sent_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['received', 'viewed', 'clicked', 'dismissed', 'unsubscribed'], 'boolean'],
            [['wpn_subscriber_id', 'wpn_push_id'], 'unique', 'targetAttribute' => ['wpn_subscriber_id', 'wpn_push_id']],
            [['wpn_push_id'], 'exist', 'skipOnError' => true, 'targetClass' => WpnPush::class, 'targetAttribute' => ['wpn_push_id' => 'id']],
            [['wpn_subscriber_id'], 'exist', 'skipOnError' => true, 'targetClass' => WpnSubscriber::class, 'targetAttribute' => ['wpn_subscriber_id' => 'id']],
        ];
    }

    public function getWpnPush(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WpnPush::class, ['id' => 'wpn_push_id']);
    }

    public function getWpnSubscriber(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WpnSubscriber::class, ['id' => 'wpn_subscriber_id']);
    }
}
