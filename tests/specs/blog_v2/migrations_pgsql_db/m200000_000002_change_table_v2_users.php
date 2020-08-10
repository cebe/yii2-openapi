<?php

/**
 * Table for User
 */
class m200000_000002_change_table_v2_users extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_role AS ENUM(\'admin\', \'editor\', \'reader\')');
        $this->addColumn('{{%v2_users}}', 'login', $this->text()->notNull()->unique());
        $this->dropColumn('{{%v2_users}}', 'username');
        $this->alterColumn('{{%v2_users}}', 'created_at', "DROP DEFAULT");
        $this->alterColumn('{{%v2_users}}', 'email', $this->text());
        $this->createIndex('unique_email', '{{%v2_users}}', 'email', true);
        $this->alterColumn('{{%v2_users}}', 'password', $this->string());
        $this->alterColumn('{{%v2_users}}', 'role', 'enum_role USING role::enum_role');
        $this->alterColumn('{{%v2_users}}', 'role', "DROP DEFAULT");
    }

    public function safeDown()
    {
        $this->alterColumn('{{%v2_users}}', 'role', $this->string(20));
        $this->alterColumn('{{%v2_users}}', 'password', $this->string(255));
        $this->dropIndex('unique_email', '{{%v2_users}}');
        $this->alterColumn('{{%v2_users}}', 'email', $this->string(200));
        $this->addColumn('{{%v2_users}}', 'username', $this->string(200)->notNull());
        $this->dropColumn('{{%v2_users}}', 'login');
        $this->alterColumn('{{%v2_users}}', 'created_at', "SET DEFAULT 'CURRENT_TIMESTAMP'");
        $this->alterColumn('{{%v2_users}}', 'role', "SET DEFAULT 'reader'");
        $this->execute('DROP TYPE enum_role');
    }
}
