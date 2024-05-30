<?php

/**
 * Table for Newcolumn
 */
class m200000_000001_change_table_newcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%newcolumns}}', 'new_column', 'enum("ONE", "TWO", "THREE") NOT NULL DEFAULT \'ONE\' AFTER id');
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN new_column_x varchar(10) NOT NULL DEFAULT \'ONE\'')->execute();
        $this->dropColumn('{{%newcolumns}}', 'delete_col');
    }

    public function down()
    {
        $this->addColumn('{{%newcolumns}}', 'delete_col', 'enum("FOUR", "FIVE", "SIX") NULL DEFAULT NULL');
        $this->dropColumn('{{%newcolumns}}', 'new_column_x');
        $this->dropColumn('{{%newcolumns}}', 'new_column');
    }
}
