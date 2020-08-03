<?php

/**
 * Table for User
 */
class m200000_000001_change_table_v2_users extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_users}}', 'login', $this->text()->notNull()->unique());
        $this->dropColumn('{{%v2_users}}', 'username');
        $this->alterColumn('{{%v2_users}}', 'created_at', $this->timestamp()->null()->defaultValue(null));
        $this->createIndex('unique_email', '{{%v2_users}}', 'email', true);
        $this->alterColumn('{{%v2_users}}', 'email', $this->text()->notNull());
        $this->alterColumn('{{%v2_users}}', 'password', $this->string()->notNull());
        $this->alterColumn('{{%v2_users}}', 'role', "enum('admin', 'editor', 'reader') NULL DEFAULT NULL");
    }

    public function down()
    {
        $this->alterColumn('{{%v2_users}}', 'role', $this->string(20)->null()->defaultValue("reader"));
        $this->alterColumn('{{%v2_users}}', 'password', $this->string(255)->notNull());
        $this->alterColumn('{{%v2_users}}', 'email', $this->string(200)->notNull());
        $this->dropIndex('unique_email', '{{%v2_users}}');
        $this->alterColumn('{{%v2_users}}', 'created_at', $this->timestamp()->null()->defaultExpression("CURRENT_TIMESTAMP"));
        $this->addColumn('{{%v2_users}}', 'username', $this->string(200)->notNull());
        $this->dropColumn('{{%v2_users}}', 'login');
    }
}
