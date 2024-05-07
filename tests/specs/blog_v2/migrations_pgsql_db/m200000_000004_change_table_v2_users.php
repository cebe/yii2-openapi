<?php

/**
 * Table for User
 */
class m200000_000004_change_table_v2_users extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE "enum_itt_v2_users_role" AS ENUM(\'admin\', \'editor\', \'reader\')');
        $this->addColumn('{{%v2_users}}', 'login', $this->text()->notNull());
        $this->dropColumn('{{%v2_users}}', 'username');
        $this->db->createCommand('ALTER TABLE {{%v2_users}} ALTER COLUMN "email" SET DATA TYPE varchar(255)')->execute();
        $this->alterColumn('{{%v2_users}}', 'role', '"enum_itt_v2_users_role" USING "role"::"enum_itt_v2_users_role"');
        $this->alterColumn('{{%v2_users}}', 'role', "DROP DEFAULT");
        $this->alterColumn('{{%v2_users}}', 'created_at', "DROP DEFAULT");
        $this->dropIndex('v2_users_username_key', '{{%v2_users}}');
        $this->createIndex('v2_users_login_key', '{{%v2_users}}', 'login', true);
        $this->createIndex('v2_users_role_flags_hash_index', '{{%v2_users}}', ["role", "flags"], 'hash');
    }

    public function safeDown()
    {
        $this->dropIndex('v2_users_role_flags_hash_index', '{{%v2_users}}');
        $this->dropIndex('v2_users_login_key', '{{%v2_users}}');
        $this->createIndex('v2_users_username_key', '{{%v2_users}}', 'username', true);
        $this->alterColumn('{{%v2_users}}', 'role', 'varchar(20) NULL USING "role"::varchar');
        $this->alterColumn('{{%v2_users}}', 'email', $this->string(200)->notNull());
        $this->addColumn('{{%v2_users}}', 'username', $this->string(200)->notNull());
        $this->dropColumn('{{%v2_users}}', 'login');
        $this->alterColumn('{{%v2_users}}', 'role', "SET DEFAULT 'reader'");
        $this->execute('DROP TYPE "enum_itt_v2_users_role"');
        $this->alterColumn('{{%v2_users}}', 'created_at', "SET DEFAULT CURRENT_TIMESTAMP");
    }
}
