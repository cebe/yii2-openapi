<?php

/**
 * Table for User
 */
class m200000_000001_create_table_v2_users extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%v2_users}}', [
            'id' => $this->bigPrimaryKey(),
            'login' => $this->text()->notNull(),
            'email' => $this->text()->notNull(),
            'password' => $this->string()->notNull(),
            'role' => 'enum(\'admin\', \'editor\', \'reader\') NULL DEFAULT NULL',
            'flags' => $this->integer()->null()->defaultValue(0),
            'created_at' => $this->timestamp()->null()->defaultValue(null),
        ]);
        $this->createIndex('v2_users_login_key', '{{%v2_users}}', 'login', true);
        $this->createIndex('v2_users_email_key', '{{%v2_users}}', 'email', true);
        $this->createIndex('v2_users_role_flags_hash_index', '{{%v2_users}}', 'role,flags', 'hash');
    }

    public function down()
    {
        $this->dropIndex('v2_users_role_flags_hash_index', '{{%v2_users}}');
        $this->dropIndex('v2_users_email_key', '{{%v2_users}}');
        $this->dropIndex('v2_users_login_key', '{{%v2_users}}');
        $this->dropTable('{{%v2_users}}');
    }
}
