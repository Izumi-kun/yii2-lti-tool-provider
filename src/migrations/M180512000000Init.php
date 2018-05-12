<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2018 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\migrations;

use yii\db\Migration;

/**
 * Initializes LTI tables.
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class M180512000000Init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $consumer = '{{%lti2_consumer}}';
        $this->createTable($consumer, [
            'consumer_pk' => $this->primaryKey(),
            'name' => $this->string(256)->notNull(),
            'consumer_key256' => $this->string(256)->notNull()->unique(),
            'consumer_key' => $this->text()->null(),
            'secret' => $this->string(1024)->notNull(),
            'lti_version' => $this->string(10)->null(),
            'consumer_name' => $this->string(255)->null(),
            'consumer_version' => $this->string(255)->null(),
            'consumer_guid' => $this->string(1024)->null(),
            'profile' => $this->text()->null(),
            'tool_proxy' => $this->text()->null(),
            'settings' => $this->text()->null(),
            'protected' => $this->smallInteger(1)->notNull(),
            'enabled' => $this->smallInteger(1)->notNull(),
            'enable_from' => $this->dateTime()->null(),
            'enable_until' => $this->dateTime()->null(),
            'last_access' => $this->date()->null(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $proxy = '{{%lti2_tool_proxy}}';
        $this->createTable($proxy, [
            'tool_proxy_pk' => $this->primaryKey(),
            'tool_proxy_id' => $this->string(32)->notNull()->unique(),
            'consumer_pk' => $this->integer()->notNull(),
            'tool_proxy' => $this->text()->notNull(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("fk-tool_proxy-consumer_pk", $proxy, 'consumer_pk', $consumer, 'consumer_pk');
        $this->createIndex("idx-tool_proxy-consumer_pk", $proxy, 'consumer_pk');

        $nonce = '{{%lti2_nonce}}';
        $this->createTable($nonce, [
            'consumer_pk' => $this->integer()->notNull(),
            'value' => $this->string(32)->notNull(),
            'expires' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey("pk-nonce", $nonce, ['consumer_pk', 'value']);
        $this->addForeignKey("fk-nonce-consumer_pk", $nonce, 'consumer_pk', $consumer, 'consumer_pk');

        $context = '{{%lti2_context}}';
        $this->createTable($context, [
            'context_pk' => $this->primaryKey(),
            'consumer_pk' => $this->integer()->notNull(),
            'lti_context_id' => $this->string(255)->notNull(),
            'settings' => $this->text()->null(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("fk-context-consumer_pk", $context, 'consumer_pk', $consumer, 'consumer_pk');
        $this->createIndex("idx-context-consumer_pk", $context, 'consumer_pk');

        $resource = '{{%lti2_resource_link}}';
        $this->createTable($resource, [
            'resource_link_pk' => $this->primaryKey(),
            'context_pk' => $this->integer()->null(),
            'consumer_pk' => $this->integer()->null(),
            'lti_resource_link_id' => $this->string(255)->notNull(),
            'settings' => $this->text(),
            'primary_resource_link_pk' => $this->integer()->null(),
            'share_approved' => $this->smallInteger(1)->null(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("fk-resource-context_pk", $resource, 'context_pk', $context, 'context_pk');
        $this->addForeignKey("fk-resource-consumer_pk", $resource, 'consumer_pk', $consumer, 'consumer_pk');
        $this->addForeignKey("fk-resource-primary_resource_link_pk", $resource, 'primary_resource_link_pk', $resource, 'resource_link_pk');
        $this->createIndex("idx-resource-consumer_pk", $resource, 'consumer_pk');
        $this->createIndex("idx-resource-context_pk", $resource, 'context_pk');

        $user = '{{%lti2_user_result}}';
        $this->createTable($user, [
            'user_pk' => $this->primaryKey(),
            'resource_link_pk' => $this->integer()->notNull(),
            'lti_user_id' => $this->string(255)->notNull(),
            'lti_result_sourcedid' => $this->string(1024)->notNull(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("fk-user-resource_link_pk", $user, 'resource_link_pk', $resource, 'resource_link_pk');
        $this->createIndex("idx-user-resource_link_pk", $user, 'resource_link_pk');

        $shareKey = '{{%lti2_share_key}}';
        $this->createTable($shareKey, [
            'share_key_id' => $this->string(32)->notNull(),
            'resource_link_pk' => $this->integer()->notNull(),
            'auto_approve' => $this->smallInteger(1)->notNull(),
            'expires' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addPrimaryKey("pk-share_key", $shareKey, 'share_key_id');
        $this->addForeignKey("fk-share_key-resource_link_pk", $shareKey, 'resource_link_pk', $resource, 'resource_link_pk');
        $this->createIndex("idx-share_key-resource_link_pk", $shareKey, 'resource_link_pk');
    }

    public function down()
    {
        $this->dropTable('{{%lti2_share_key}}');
        $this->dropTable('{{%lti2_user_result}}');
        $this->dropTable('{{%lti2_resource_link}}');
        $this->dropTable('{{%lti2_context}}');
        $this->dropTable('{{%lti2_nonce}}');
        $this->dropTable('{{%lti2_tool_proxy}}');
        $this->dropTable('{{%lti2_consumer}}');
    }
}
