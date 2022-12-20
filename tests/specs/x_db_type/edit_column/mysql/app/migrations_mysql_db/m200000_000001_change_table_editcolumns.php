<?php

/**
 * Table for Editcolumn
 */
class m200000_000001_change_table_editcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand("ALTER TABLE {{%editcolumns}} ADD COLUMN first_name varchar(255) NULL DEFAULT NULL")->execute();
        $this->alterColumn('{{%editcolumns}}', 'dec_col', $this->decimal(12,2)->null()->defaultValue("3.14"));
        $this->alterColumn('{{%editcolumns}}', 'json_col', $this->text()->notNull());
        $this->alterColumn('{{%editcolumns}}', 'name', $this->string(255)->notNull()->defaultValue("Horse-2"));
        $this->alterColumn('{{%editcolumns}}', 'numeric_col', $this->double()->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', $this->string(255)->notNull());
        $this->alterColumn('{{%editcolumns}}', 'string_col', $this->text()->null());
    }

    public function down()
    {
        $this->alterColumn('{{%editcolumns}}', 'string_col', $this->string(255)->notNull());
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', $this->string(255)->null()->defaultValue("hi there"));
        $this->alterColumn('{{%editcolumns}}', 'numeric_col', $this->integer(11)->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'name', $this->string(255)->notNull()->defaultValue("Horse"));
        $this->alterColumn('{{%editcolumns}}', 'json_col', 'json NULL');
        $this->alterColumn('{{%editcolumns}}', 'dec_col', $this->decimal(12,4)->null()->defaultValue(null));
        $this->dropColumn('{{%editcolumns}}', 'first_name');
    }
}
