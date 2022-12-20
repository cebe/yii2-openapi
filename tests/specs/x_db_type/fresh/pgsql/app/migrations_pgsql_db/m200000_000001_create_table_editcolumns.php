<?php

/**
 * Table for Editcolumn
 */
class m200000_000001_create_table_editcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%editcolumns}}', [
            'id' => $this->primaryKey(),
            0 => 'name varchar(254) NOT NULL DEFAULT \'Horse-2\'',
            'tag' => $this->text()->null()->defaultValue(null),
            1 => 'first_name varchar NULL DEFAULT NULL',
            'string_col' => $this->text()->null()->defaultValue(null),
            2 => 'dec_col decimal(12,2) NULL DEFAULT 3.14',
            3 => 'str_col_def varchar NOT NULL',
            4 => 'json_col text NOT NULL DEFAULT \'fox jumps over dog\'',
            5 => 'json_col_2 json NOT NULL DEFAULT \'[]\'',
            6 => 'numeric_col double precision NULL DEFAULT NULL',
            7 => 'json_col_def_n json NOT NULL DEFAULT \'[]\'',
            8 => 'json_col_def_n_2 json NOT NULL DEFAULT \'[]\'',
            9 => 'text_col_array text[] NULL DEFAULT NULL',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%editcolumns}}');
    }
}
