<?php

/**
 * Table for Alldbdatatype
 */
class m200000_000000_create_table_alldbdatatypes extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%alldbdatatypes}}', [
            'id' => $this->bigPrimaryKey(),
            0 => 'string_col varchar(255) NULL DEFAULT NULL',
            1 => 'varchar_col varchar(132) NULL DEFAULT NULL',
            2 => 'text_col text NULL',
            3 => 'varchar_4_col varchar(4) NULL DEFAULT NULL',
            4 => 'char_4_col char(4) NULL DEFAULT NULL',
            5 => 'char_5_col char NULL DEFAULT NULL',
            6 => 'char_6_col char NOT NULL',
            7 => 'char_7_col char(6) NOT NULL',
            8 => 'char_8_col char NULL DEFAULT \'d\'',
            9 => 'decimal_col decimal(12,3) NULL DEFAULT NULL',
            10 => 'varbinary_col varbinary(5) NULL DEFAULT NULL',
            11 => 'blob_col blob NULL',
            12 => 'bit_col bit NULL DEFAULT NULL',
            13 => 'bit_2 bit(1) NULL DEFAULT NULL',
            14 => 'bit_3 bit(64) NULL DEFAULT NULL',
            15 => 'ti tinyint NULL DEFAULT NULL',
            16 => 'ti_2 tinyint(1) NULL DEFAULT NULL',
            17 => 'ti_3 tinyint(2) NULL DEFAULT NULL',
            18 => 'si_col smallint NULL DEFAULT NULL',
            19 => 'si_col_2 smallint unsigned zerofill NULL DEFAULT NULL',
            20 => 'mi mediumint(10) unsigned zerofill comment "comment" NULL DEFAULT 7',
            21 => 'bi bigint NULL DEFAULT NULL',
            22 => 'int_col int NULL DEFAULT NULL',
            23 => 'int_col_2 integer NULL DEFAULT NULL',
            24 => 'numeric_col numeric NULL DEFAULT NULL',
            25 => 'float_col float NULL DEFAULT NULL',
            26 => 'float_2 float(10, 2) NULL DEFAULT NULL',
            27 => 'float_3 float(8) NULL DEFAULT NULL',
            28 => 'double_col double NULL DEFAULT NULL',
            29 => 'double_p double precision(10,2) NULL DEFAULT NULL',
            30 => 'double_p_2 double precision NULL DEFAULT NULL',
            31 => 'real_col real NULL DEFAULT NULL',
            32 => 'date_col date NULL DEFAULT NULL',
            33 => 'time_col time NULL DEFAULT NULL',
            34 => 'datetime_col datetime NULL DEFAULT NULL',
            35 => 'timestamp_col timestamp NULL DEFAULT NULL',
            36 => 'year_col year NULL DEFAULT NULL',
            37 => 'json_col json NOT NULL',
            38 => 'json_col_def json NOT NULL',
            39 => 'json_col_def_2 json NOT NULL',
            40 => 'blob_def blob NULL',
            41 => 'text_def text NULL',
            42 => 'json_def json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%alldbdatatypes}}');
    }
}
