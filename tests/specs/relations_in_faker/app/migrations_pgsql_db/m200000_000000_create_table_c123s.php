<?php

/**
 * Table for C123
 */
class m200000_000000_create_table_c123s extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%c123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null()->defaultValue(null),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%c123s}}');
    }
}
