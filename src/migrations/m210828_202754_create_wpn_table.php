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
        $this->createTable('{{%wpn_app}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'host' => $this->string(180)->unique()->notNull(),
            'private_key' => $this->string(50)->notNull(),
            'public_key' => $this->string(100)->notNull(),
            'subject' => $this->string(255)->notNull(),
            'enabled' => $this->boolean()->notNull(),
            'icon' => $this->string()->null(),
            'badge' => $this->string()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->createTable('{{%wpn_subscription}}', [
            'id' => $this->primaryKey(),
            'endpoint' => $this->string(255)->notNull(),
            'auth' => $this->string()->notNull(),
            'public_key' => $this->string()->notNull(),
            'content_encoding' => $this->string()->notNull(),
            'subscribed' => $this->boolean()->notNull(),
            'test_user' => $this->boolean(),
            'yii_user_id' => $this->integer(),
            'app_id' => $this->integer()->notNull(),
            'ua' => $this->string(),
            'ip' => $this->string()->notNull(),
            'os' => $this->string(),
            'browser' => $this->string(),
            'last_seen' => $this->dateTime(),
            'last_error' => $this->string(),
            'reason' => $this->string(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');
        $this->createIndex('uq_wpn_subscription_endpoint', '{{%wpn_subscription}}', 'endpoint', true);
        $this->createIndex('uq_wpn_subscription_yii_user_id', '{{%wpn_subscription}}', 'yii_user_id');
        $this->createIndex('uq_wpn_subscription_subscribed', '{{%wpn_subscription}}', 'subscribed');
        $this->createIndex('uq_wpn_subscription_app_id', '{{%wpn_subscription}}', 'app_id');

        $this->addForeignKey('fk_wpn_subscription_app', '{{%wpn_subscription}}', 'app_id', '{{%wpn_app}}', 'id');

        $this->createTable('{{%wpn_campaign}}', [
            'id' => $this->primaryKey(),
            'app_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'tag' => $this->string(180)->unique()->notNull(),
            'body' => $this->string()->notNull(),
            'url' => $this->string(),
            'image' => $this->string(),
            'test_only' => $this->boolean(),
            'created_at' => $this->dateTime()->notNull(),
            'scheduled_at' => $this->dateTime()->notNull(),
            'started_at' => $this->dateTime(),
            'finished_at' => $this->dateTime(),
            'updated_at' => $this->dateTime()->notNull(),
            'extra' => $this->text(),
        ]);
        $this->createIndex('uq_wpn_campaign_app_id', '{{%wpn_campaign}}', 'app_id');
        $this->addForeignKey('fk_wpn_campaign_app', '{{%wpn_campaign}}', 'app_id', '{{%wpn_app}}', 'id');

        $this->createTable('{{%wpn_report}}', [
            'id' => $this->primaryKey(),
            'campaign_id' => $this->integer()->notNull(),
            'subscription_id' => $this->integer()->notNull(),
            'sent_at' => $this->dateTime()->notNull(),
            'received' => $this->boolean(),
            'viewed' => $this->boolean(),
            'clicked' => $this->boolean(),
            'dismissed' => $this->boolean(),
        ]);
        $this->createIndex('uq_wpn_report', '{{%wpn_report}}', ['subscription_id', 'campaign_id'], true);

        $this->createIndex('idx_wpn_report_campaign', '{{%wpn_report}}','campaign_id');
        $this->addForeignKey('fk_wpn_report_campaign', '{{%wpn_report}}','campaign_id', '{{%wpn_campaign}}', 'id');

        $this->createIndex('idx_wpn_report_subscription', '{{%wpn_report}}','subscription_id');
        $this->addForeignKey('fk_wpn_report_subscription', '{{%wpn_report}}','subscription_id', '{{%wpn_subscription}}', 'id');
    }
    
    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%wpn_report}}');
        $this->dropTable('{{%wpn_campaign}}');
        $this->dropTable('{{%wpn_subscription}}');
        $this->dropTable('{{%wpn_app}}');
    }
}
