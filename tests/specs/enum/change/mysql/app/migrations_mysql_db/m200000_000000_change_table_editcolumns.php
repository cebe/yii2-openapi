<?php

/**
 * Table for Editcolumn
 */
class m200000_000000_change_table_editcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%editcolumns}}', 'connection', 'enum("WIRED", "WIRELESS") NOT NULL DEFAULT \'WIRED\'');
        $this->alterColumn('{{%editcolumns}}', 'device', $this->text()->null());
    }

    public function down()
    {
        $this->alterColumn('{{%editcolumns}}', 'device', 'enum("MOBILE", "TV", "COMPUTER") NOT NULL DEFAULT \'TV\'');
        $this->alterColumn('{{%editcolumns}}', 'connection', $this->string(255)->null()->defaultValue(null));
    }
}
