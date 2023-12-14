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
            'name' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%users}}');
    }
}
