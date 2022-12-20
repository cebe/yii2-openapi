<?php

/**
 * Table for Newcolumn
 */
class m200000_000002_create_table_newcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%newcolumns}}', [
            'id' => $this->primaryKey(),
            0 => 'name varchar(255) NOT NULL',
            'last_name' => $this->text()->null(),
            1 => 'dec_col decimal(12,4) NULL DEFAULT NULL',
            2 => 'json_col json NOT NULL',
            3 => 'varchar_col varchar(5) NULL DEFAULT NULL',
            4 => 'numeric_col double precision NULL DEFAULT NULL',
            5 => 'json_col_def_n json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%newcolumns}}');
    }
}
