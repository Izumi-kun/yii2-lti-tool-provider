<?php
/** @noinspection PhpUnused */

/**
 * @link https://github.com/Izumi-kun/yii2-lti-tool-provider
 * @copyright Copyright (c) 2025 Viktor Khokhryakov
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace izumi\yii2lti\migrations\platform;

use yii\db\Migration;

/**
 * Initializes LTI tables for Platform.
 *
 * @author Viktor Khokhryakov <viktor.khokhryakov@gmail.com>
 */
class M240000000001Init extends Migration
{
    public function up(): void
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        $tool = '{{%lti2_tool}}';
        $this->createTable($tool, [
            'tool_pk' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'consumer_key' => $this->string(255)->null(),
            'secret' => $this->string(1024)->null(),
            'message_url' => $this->string(255)->null(),
            'initiate_login_url' => $this->string(255)->null(),
            'redirection_uris' => $this->text()->null(),
            'public_key' => $this->text()->null(),
            'lti_version' => $this->string(10)->null(),
            'signature_method' => $this->string(15)->null(),
            'settings' => $this->text()->null(),
            'enabled' => $this->smallInteger(1)->notNull(),
            'enable_from' => $this->dateTime()->null(),
            'enable_until' => $this->dateTime()->null(),
            'last_access' => $this->date()->null(),
            'created' => $this->dateTime()->notNull(),
            'updated' => $this->dateTime()->notNull(),
        ], $tableOptions);
        $this->createIndex('lti2_tool_initiate_login_url_UNIQUE', $tool, ['initiate_login_url'], true);
    }

    public function down(): void
    {
        $this->dropTable('{{%lti2_tool}}');
    }
}
