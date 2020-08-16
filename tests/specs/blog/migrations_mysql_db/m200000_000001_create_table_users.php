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
            'username' => $this->string(200)->notNull()->unique(),
            'email' => $this->string(200)->notNull()->unique(),
            'password' => $this->string()->notNull(),
            'role' => $this->string(20)->null()->defaultValue("reader"),
            'flags' => $this->integer()->null()->defaultValue(0),
            'created_at' => $this->timestamp()->null()->defaultExpression("CURRENT_TIMESTAMP"),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%users}}');
    }
}
