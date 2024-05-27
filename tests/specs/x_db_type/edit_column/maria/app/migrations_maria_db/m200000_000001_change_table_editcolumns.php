<?php

/**
 * Table for Editcolumn
 */
class m200000_000001_change_table_editcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN first_name varchar(255) NULL DEFAULT NULL AFTER tag')->execute();
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN json_col_def_n json NOT NULL DEFAULT \'[]\' AFTER numeric_col')->execute();
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN json_col_def_n_2 json NOT NULL DEFAULT \'[]\'')->execute();
        $this->alterColumn('{{%editcolumns}}', 'name', $this->string(255)->notNull()->defaultValue('Horse-2'));
        $this->alterColumn('{{%editcolumns}}', 'string_col', $this->text()->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'dec_col', $this->decimal(12,2)->null()->defaultValue("3.14"));
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', $this->string(3)->notNull());
        $this->alterColumn('{{%editcolumns}}', 'json_col', $this->text()->notNull()->defaultValue('fox jumps over dog'));
        $this->alterColumn('{{%editcolumns}}', 'json_col_2', 'json NOT NULL DEFAULT \'[]\'');
        $this->alterColumn('{{%editcolumns}}', 'numeric_col', $this->double()->null()->defaultValue(null));
    }

    public function down()
    {
        $this->alterColumn('{{%editcolumns}}', 'numeric_col', $this->integer(11)->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'json_col_2', 'json NULL DEFAULT NULL');
        $this->alterColumn('{{%editcolumns}}', 'json_col', 'json NULL DEFAULT NULL');
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', $this->string(255)->null()->defaultValue('hi there'));
        $this->alterColumn('{{%editcolumns}}', 'dec_col', $this->decimal(12,4)->null()->defaultValue(null));
        $this->alterColumn('{{%editcolumns}}', 'string_col', $this->string(255)->notNull());
        $this->alterColumn('{{%editcolumns}}', 'name', $this->string(255)->notNull()->defaultValue('Horse'));
        $this->dropColumn('{{%editcolumns}}', 'json_col_def_n_2');
        $this->dropColumn('{{%editcolumns}}', 'json_col_def_n');
        $this->dropColumn('{{%editcolumns}}', 'first_name');
    }
}
