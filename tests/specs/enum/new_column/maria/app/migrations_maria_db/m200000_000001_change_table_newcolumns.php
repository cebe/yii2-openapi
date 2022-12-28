<?php

/**
 * Table for Newcolumn
 */
class m200000_000001_change_table_newcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%newcolumns}}', 'new_column', 'enum("ONE", "TWO", "THREE") NOT NULL DEFAULT \'ONE\'');
        $this->dropColumn('{{%newcolumns}}', 'delete_col');
    }

    public function down()
    {
        $this->addColumn('{{%newcolumns}}', 'delete_col', 'enum("FOUR", "FIVE", "SIX") NULL DEFAULT NULL');
        $this->dropColumn('{{%newcolumns}}', 'new_column');
    }
}
