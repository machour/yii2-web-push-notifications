<?php

namespace machour\yii2\wpn\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "wpn_push".
 *
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string $icon
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
 * @property WpnSubscriberPush[] $wpnSubscriberPushes
 * @property WpnSubscriber[] $wpnSubscribers
 *
 * @property-read array $options
 */
class WpnPush extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%wpn_push}}';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => 'created_at',
                    self::EVENT_BEFORE_UPDATE => 'updated_at',
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['title', 'body', 'scheduled_at', 'tag'], 'required'],
            [['scheduled_at', 'started_at', 'finished_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['extra'], 'string'],
            [['title', 'body', 'icon', 'url', 'image', 'tag'], 'string', 'max' => 255],
        ];
    }

    public function getWpnSubscriberPushes(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnSubscriberPush::class, ['wpn_push_id' => 'id']);
    }

    public function getWpnSubscribers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(WpnSubscriber::class, ['id' => 'wpn_subscriber_id'])->via('wpnSubscriberPushes');
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
                'push_id' => $this->id,
            ],
        ];
    }
}
