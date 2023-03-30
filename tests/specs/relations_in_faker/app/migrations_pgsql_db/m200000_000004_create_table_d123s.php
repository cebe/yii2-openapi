<?php

/**
 * Table for D123
 */
class m200000_000004_create_table_d123s extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%d123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null()->defaultValue(null),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%d123s}}');
    }
}
