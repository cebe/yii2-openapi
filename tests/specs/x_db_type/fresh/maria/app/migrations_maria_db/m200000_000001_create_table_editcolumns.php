<?php

/**
 * Table for Editcolumn
 */
class m200000_000001_create_table_editcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%editcolumns}}', [
            'id' => $this->primaryKey(),
            0 => 'name varchar(255) NOT NULL DEFAULT \'Horse-2\'',
            'tag' => $this->text()->null()->defaultValue(null),
            1 => 'first_name varchar(255) NULL DEFAULT NULL',
            'string_col' => $this->text()->null()->defaultValue(null),
            2 => 'dec_col decimal(12,2) NULL DEFAULT 3.14',
            3 => 'str_col_def varchar(3) NOT NULL',
            4 => 'json_col text NOT NULL DEFAULT \'fox jumps over dog\'',
            5 => 'numeric_col double precision NULL DEFAULT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%editcolumns}}');
    }
}
