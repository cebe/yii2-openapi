<?php

/**
 * Table for ColumnNameChange
 */
class m200000_000000_change_table_column_name_changes extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->db->createCommand('ALTER TABLE {{%column_name_changes}} ADD COLUMN updated_at_2 timestamp NOT NULL')->execute();
        $this->dropColumn('{{%column_name_changes}}', 'updated_at');
    }

    public function safeDown()
    {
        $this->addColumn('{{%column_name_changes}}', 'updated_at', $this->timestamp()->notNull());
        $this->dropColumn('{{%column_name_changes}}', 'updated_at_2');
    }
}
