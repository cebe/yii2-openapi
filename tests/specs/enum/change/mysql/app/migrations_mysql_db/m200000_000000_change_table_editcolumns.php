<?php

/**
 * Table for Editcolumn
 */
class m200000_000000_change_table_editcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%editcolumns}}', 'device', $this->text()->null());
        $this->alterColumn('{{%editcolumns}}', 'connection', 'enum("WIRED", "WIRELESS") NOT NULL DEFAULT \'WIRED\'');
        $this->alterColumn('{{%editcolumns}}', 'camelCaseCol', 'enum("ONE", "TWO", "THREE") NOT NULL DEFAULT \'TWO\'');
    }

    public function down()
    {
        $this->alterColumn('{{%editcolumns}}', 'camelCaseCol', $this->string(255)->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'connection', $this->string(255)->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'device', 'enum("MOBILE", "TV", "COMPUTER") NOT NULL DEFAULT \'TV\'');
    }
}
