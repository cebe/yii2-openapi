<?php

/**
 * Table for User
 */
class m200000_000004_change_table_v2_users extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_users}}', 'login', $this->text()->notNull()->after('id'));
        $this->dropColumn('{{%v2_users}}', 'username');
        $this->alterColumn('{{%v2_users}}', 'email', $this->string(255)->notNull());
        $this->alterColumn('{{%v2_users}}', 'role', 'enum("admin", "editor", "reader") NULL DEFAULT NULL');
        $this->alterColumn('{{%v2_users}}', 'created_at', $this->timestamp()->null()->defaultValue(null));
        $this->dropIndex('v2_users_username_key', '{{%v2_users}}');
        $this->createIndex('v2_users_login_key', '{{%v2_users}}', 'login', true);
        $this->createIndex('v2_users_role_flags_hash_index', '{{%v2_users}}', ["role", "flags"], 'hash');
    }

    public function down()
    {
        $this->dropIndex('v2_users_role_flags_hash_index', '{{%v2_users}}');
        $this->dropIndex('v2_users_login_key', '{{%v2_users}}');
        $this->createIndex('v2_users_username_key', '{{%v2_users}}', 'username', true);
        $this->alterColumn('{{%v2_users}}', 'created_at', $this->timestamp()->null()->defaultExpression("current_timestamp()"));
        $this->alterColumn('{{%v2_users}}', 'role', $this->string(20)->null()->defaultValue('reader'));
        $this->alterColumn('{{%v2_users}}', 'email', $this->string(200)->notNull());
        $this->addColumn('{{%v2_users}}', 'username', $this->string(200)->notNull());
        $this->dropColumn('{{%v2_users}}', 'login');
    }
}
