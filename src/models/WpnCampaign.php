<?php

namespace machour\yii2\wpn\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "wpn_campaign".
 *
 * @property int $id
 * @property int $app_id
 * @property string $title
 * @property string $body
 * @property string $url
 * @property string $image
 * @property string $created_at
 * @property string $scheduled_at
 * @property string $started_at
 * @property string $finished_at
 * @property string $updated_at
 * @property string $extra
 * @property string $tag
 *
 * @property WpnReport[] $reports
 * @property WpnSubscription[] $subscriptions
 * @property WpnApp $app
 *
 * @property-read array $options
 */
class WpnCampaign extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%wpn_campaign}}';
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
            [['title', 'body', 'scheduled_at', 'tag', 'app_id'], 'required'],
            [['app_id'], 'integer'],
            [['scheduled_at', 'started_at', 'finished_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['extra'], 'string'],
            [['title', 'body', 'url', 'image', 'tag'], 'string', 'max' => 255],
            [['app_id'], 'exist', 'skipOnError' => true, 'targetClass' => WpnApp::class, 'targetAttribute' => ['app_id' => 'id']],
        ];
    }

    public function getReports(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnReport::class, ['campaign_id' => 'id']);
    }

    public function getSubscriptions(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnSubscription::class, ['id' => 'subscription_id'])->via('reports');
    }

    public function getApp(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WpnApp::class, ['id' => 'app_id']);
    }

    public function getOptions(): array
    {
        return [
            'title' => $this->title,
            'image' => $this->image,
            'body' => $this->body,
            'tag' => $this->tag,
            'data' => [
                'url' => $this->url,
                'campaignId' => $this->id,
            ],
        ];
    }
}
