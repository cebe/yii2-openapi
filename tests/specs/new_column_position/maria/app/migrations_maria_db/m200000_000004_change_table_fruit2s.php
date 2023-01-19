<?php

/**
 * Table for Fruit2
 */
class m200000_000004_change_table_fruit2s extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%fruit2s}} ADD COLUMN name text NOT NULL FIRST')->execute();
    }

    public function down()
    {
        $this->dropColumn('{{%fruit2s}}', 'name');
    }
}
