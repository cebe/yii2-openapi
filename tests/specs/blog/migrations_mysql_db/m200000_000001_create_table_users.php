<?php

/**
 * Table for User
 */
class m200000_000001_create_table_users extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(200)->notNull(),
            'email' => $this->string(200)->notNull(),
            'password' => $this->string()->notNull(),
            'flags' => $this->integer()->null()->defaultValue(0),
            'created_at' => $this->timestamp()->null()->defaultExpression("CURRENT_TIMESTAMP"),
        ]);
        $this->createIndex('users_username_key', '{{%users}}', 'username', true);
        $this->createIndex('users_email_key', '{{%users}}', 'email', true);
        $this->createIndex('users_flags_index', '{{%users}}', 'flags', false);
    }

    public function down()
    {
        $this->dropIndex('users_flags_index', '{{%users}}');
        $this->dropIndex('users_email_key', '{{%users}}');
        $this->dropIndex('users_username_key', '{{%users}}');
        $this->dropTable('{{%users}}');
    }
}
