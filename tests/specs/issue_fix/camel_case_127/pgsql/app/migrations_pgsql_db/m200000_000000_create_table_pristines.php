<?php

/**
 * Table for Pristine
 */
class m200000_000000_create_table_pristines extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%pristines}}', [
            'id' => $this->primaryKey(),
            'firstName' => $this->text()->null()->defaultValue(null),
            0 => '"newColumn" varchar(255) NULL DEFAULT NULL',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%pristines}}');
    }
}
