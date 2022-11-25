<?php

/**
 * Table for User
 */
class m200000_000004_change_table_v2_users extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%v2_users}}', 'login', $this->text()->notNull());
        $this->dropColumn('{{%v2_users}}', 'username');
        $this->alterColumn('{{%v2_users}}', 'created_at', "DROP DEFAULT");
        $this->alterColumn('{{%v2_users}}', 'email', $this->text()->notNull());
        $this->dropIndex('v2_users_username_key', '{{%v2_users}}');
        $this->createIndex('v2_users_login_key', '{{%v2_users}}', 'login', true);
        $this->createIndex('v2_users_flags_hash_index', '{{%v2_users}}', 'flags', 'hash');
    }

    public function safeDown()
    {
        $this->dropIndex('v2_users_flags_hash_index', '{{%v2_users}}');
        $this->dropIndex('v2_users_login_key', '{{%v2_users}}');
        $this->createIndex('v2_users_username_key', '{{%v2_users}}', 'username', true);
        $this->alterColumn('{{%v2_users}}', 'email', $this->string(200)->notNull());
        $this->addColumn('{{%v2_users}}', 'username', $this->string(200)->notNull());
        $this->dropColumn('{{%v2_users}}', 'login');
        $this->alterColumn('{{%v2_users}}', 'created_at', "SET DEFAULT CURRENT_TIMESTAMP");
    }
}
