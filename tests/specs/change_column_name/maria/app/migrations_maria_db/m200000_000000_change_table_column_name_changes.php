<?php

/**
 * Table for ColumnNameChange
 */
class m200000_000000_change_table_column_name_changes extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%column_name_changes}} ADD COLUMN updated_at_2 datetime NOT NULL')->execute();
        $this->dropColumn('{{%column_name_changes}}', 'updated_at');
    }

    public function down()
    {
        $this->addColumn('{{%column_name_changes}}', 'updated_at', $this->datetime()->notNull());
        $this->dropColumn('{{%column_name_changes}}', 'updated_at_2');
    }
}
