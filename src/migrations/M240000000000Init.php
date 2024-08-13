<?php
/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2024 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\migrations;

use yii\db\Migration;

/**
 * Initializes LTI tables.
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class M240000000000Init extends Migration
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
            'name' => $this->string(255)->notNull(),
            'consumer_key' => $this->string(255)->notNull()->unique(),
            'secret' => $this->string(1024)->notNull(),
            'platform_id' => $this->string(255)->null(),
            'client_id' => $this->string(255)->null(),
            'deployment_id' => $this->string(255)->null(),
            'public_key' => $this->text()->null(),
            'lti_version' => $this->string(10)->null(),
            'signature_method' => $this->string(15)->notNull()->defaultValue('HMAC-SHA1'),
            'consumer_name' => $this->string(255)->null(),
            'consumer_version' => $this->string(255)->null(),
            'consumer_guid' => $this->string(255)->null(),
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
        $this->createIndex('lti2_consumer_consumer_key_UNIQUE', $consumer, ['consumer_key'], true);
        $this->createIndex('lti2_consumer_platform_UNIQUE', $consumer, ['platform_id', 'client_id', 'deployment_id'], true);

        $nonce = '{{%lti2_nonce}}';
        $this->createTable($nonce, [
            'consumer_pk' => $this->integer()->notNull(),
            'value' => $this->string(255)->notNull(),
            'expires' => $this->dateTime()->notNull(),
            'PRIMARY KEY ([[consumer_pk]], [[value]])',
        ], $tableOptions);
        $this->addForeignKey('lti2_nonce_lti2_consumer_FK1', $nonce, 'consumer_pk', $consumer, 'consumer_pk');

        $accessToken = '{{%lti2_access_token}}';
        $this->createTable($accessToken, [
            'consumer_pk' => $this->integer()->notNull(),
            'scopes' => $this->text()->notNull(),
            'token' => $this->string(2000)->notNull(),
            'expires' => $this->dateTime()->notNull(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
            'PRIMARY KEY ([[consumer_pk]])',
        ], $tableOptions);
        $this->addForeignKey('lti2_access_token_lti2_consumer_FK1', $accessToken, 'consumer_pk', $consumer, 'consumer_pk');

        $context = '{{%lti2_context}}';
        $this->createTable($context, [
            'context_pk' => $this->primaryKey(),
            'consumer_pk' => $this->integer()->notNull(),
            'title' => $this->string(255)->null(),
            'lti_context_id' => $this->string(255)->notNull(),
            'type' => $this->string(50)->null(),
            'settings' => $this->text()->null(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("lti2_context_lti2_consumer_FK1", $context, 'consumer_pk', $consumer, 'consumer_pk');
        $this->createIndex("lti2_context_consumer_id_IDX", $context, 'consumer_pk');

        $resourceLink = '{{%lti2_resource_link}}';
        $this->createTable($resourceLink, [
            'resource_link_pk' => $this->primaryKey(),
            'context_pk' => $this->integer()->null(),
            'consumer_pk' => $this->integer()->null(),
            'title' => $this->string(255)->null(),
            'lti_resource_link_id' => $this->string(255)->notNull(),
            'settings' => $this->text(),
            'primary_resource_link_pk' => $this->integer()->null(),
            'share_approved' => $this->smallInteger(1)->null(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("lti2_resource_link_lti2_consumer_FK1", $resourceLink, 'consumer_pk', $consumer, 'consumer_pk');
        $this->addForeignKey("lti2_resource_link_lti2_context_FK1", $resourceLink, 'context_pk', $context, 'context_pk');
        $this->addForeignKey("lti2_resource_link_lti2_resource_link_FK1", $resourceLink, 'primary_resource_link_pk', $resourceLink, 'resource_link_pk');
        $this->createIndex("lti2_resource_link_consumer_pk_IDX", $resourceLink, 'consumer_pk');
        $this->createIndex("lti2_resource_link_context_pk_IDX", $resourceLink, 'context_pk');

        $userResult = '{{%lti2_user_result}}';
        $this->createTable($userResult, [
            'user_result_pk' => $this->primaryKey(),
            'resource_link_pk' => $this->integer()->notNull(),
            'lti_user_id' => $this->string(255)->notNull(),
            'lti_result_sourcedid' => $this->text()->notNull(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->addForeignKey("lti2_user_result_lti2_resource_link_FK1", $userResult, 'resource_link_pk', $resourceLink, 'resource_link_pk');
        $this->createIndex("lti2_user_result_resource_link_pk_IDX", $userResult, 'resource_link_pk');

        $shareKey = '{{%lti2_share_key}}';
        $this->createTable($shareKey, [
            'share_key_id' => $this->string(32)->notNull(),
            'resource_link_pk' => $this->integer()->notNull(),
            'auto_approve' => $this->smallInteger(1)->notNull(),
            'expires' => $this->dateTime()->notNull(),
            'PRIMARY KEY ([[share_key_id]])',
        ], $tableOptions);
        $this->addForeignKey("lti2_share_key_lti2_resource_link_FK1", $shareKey, 'resource_link_pk', $resourceLink, 'resource_link_pk');
        $this->createIndex("lti2_share_key_resource_link_pk_IDX", $shareKey, 'resource_link_pk');
    }

    public function down()
    {
        $this->dropTable('{{%lti2_share_key}}');
        $this->dropTable('{{%lti2_user_result}}');
        $this->dropTable('{{%lti2_resource_link}}');
        $this->dropTable('{{%lti2_context}}');
        $this->dropTable('{{%lti2_access_token}}');
        $this->dropTable('{{%lti2_nonce}}');
        $this->dropTable('{{%lti2_consumer}}');
    }
}
