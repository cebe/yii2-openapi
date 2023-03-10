<?php

/**
 * Table for User
 */
class m200000_000000_create_table_users extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%users}}');
    }
}
