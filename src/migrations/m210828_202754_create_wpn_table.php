<?php

use yii\db\Migration;

/**
 * Handles the creation of the module tables
 */
class m210828_202754_create_wpn_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%wpn_subscriber}}', [
            'id' => $this->primaryKey(),
            'endpoint' => $this->string()->notNull(),
            'auth' => $this->string()->notNull(),
            'p256dh' => $this->string()->notNull(),
            'content_encoding' => $this->string()->notNull(),
            'subscribed' => $this->boolean()->notNull(),
            'test_user' => $this->boolean(),
            'yii_user_id' => $this->integer(),
            'app' => $this->string()->notNull(),
            'ua' => $this->string(),
            'ip' => $this->string()->notNull(),
            'os' => $this->string(),
            'browser' => $this->string(),
            'last_seen' => $this->dateTime(),
            'last_error' => $this->string(),
            'reason' => $this->string(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ]);
        $this->createIndex('uq_wpn_subscriber_endpoint', '{{%wpn_subscriber}}', 'endpoint', true);
        $this->createIndex('uq_wpn_subscriber_yii_user_id', '{{%wpn_subscriber}}', 'yii_user_id');
        $this->createIndex('uq_wpn_subscriber_subscribed', '{{%wpn_subscriber}}', 'subscribed');

        $this->createTable('{{%wpn_push}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'tag' => $this->string()->unique()->notNull(),
            'body' => $this->string()->notNull(),
            'icon' => $this->string(),
            'url' => $this->string(),
            'image' => $this->string(),
            'created_at' => $this->dateTime()->notNull(),
            'scheduled_at' => $this->dateTime()->notNull(),
            'started_at' => $this->dateTime(),
            'finished_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()->notNull(),
            'extra' => $this->text(),
        ]);

        $this->createTable('{{%wpn_subscriber_push}}', [
            'id' => $this->primaryKey(),
            'wpn_push_id' => $this->integer()->notNull(),
            'wpn_subscriber_id' => $this->integer()->notNull(),
            'sent_at' => $this->dateTime()->notNull(),
            'received' => $this->boolean(),
            'viewed' => $this->boolean(),
            'clicked' => $this->boolean(),
            'dismissed' => $this->boolean(),
            'unsubscribed' => $this->boolean(),
        ]);
        $this->createIndex('uq_wpn_subscriber_push', '{{%wpn_subscriber_push}}', ['wpn_subscriber_id', 'wpn_push_id'], true);

        $this->createIndex('idx_wpn_subscriber_push_push', '{{%wpn_subscriber_push}}','wpn_push_id');
        $this->addForeignKey('fk_wpn_subscriber_push_push', '{{%wpn_subscriber_push}}','wpn_push_id', '{{%wpn_push}}', 'id');

        $this->createIndex('idx_wpn_subscriber_push_subscriber', '{{%wpn_subscriber_push}}','wpn_subscriber_id');
        $this->addForeignKey('fk_wpn_subscriber_push_subscriber', '{{%wpn_subscriber_push}}','wpn_subscriber_id', '{{%wpn_subscriber}}', 'id');
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wpn_subscriber_push}}');
        $this->dropTable('{{%wpn_push}}');
        $this->dropTable('{{%wpn_subscriber}}');
    }
}
