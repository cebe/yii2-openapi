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
            'login' => $this->text()->notNull()->unique(),
            'email' => $this->text()->notNull()->unique(),
            'password' => $this->string()->notNull(),
            'role' => 'enum(\'admin\', \'editor\', \'reader\') NULL DEFAULT NULL',
            'flags' => $this->integer()->null()->defaultValue(0),
            'created_at' => $this->timestamp()->null()->defaultValue(null),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%v2_users}}');
    }
}
