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
            'string_col' => $this->string()->null()->defaultValue(null),
            'varchar_col' => $this->string()->null()->defaultValue(null),
            'text_col' => $this->text()->null(),
            'varchar_4_col' => $this->string()->null()->defaultValue(null),
            'char_4_col' => $this->char()->null()->defaultValue(null),
            'char_5_col' => $this->char()->null()->defaultValue(null),
            'char_6_col' => $this->char()->notNull(),
            'char_7_col' => $this->char(6)->notNull(),
            'char_8_col' => $this->char()->null()->defaultValue("d"),
            'decimal_col' => 'decimal(12,3) NULL DEFAULT NULL',
            'varbinary_col' => 'varbinary(5) NULL DEFAULT NULL',
            'blob' => 'blob NULL',
            'bit' => 'bit NULL DEFAULT NULL',
            'bit_2' => 'bit(1) NULL DEFAULT NULL',
            'bit_3' => 'bit(64) NULL DEFAULT NULL',
            'ti' => $this->tinyInteger()->null()->defaultValue(null),
            'ti_2' => $this->tinyInteger()->null()->defaultValue(null),
            'ti_3' => $this->tinyInteger()->null()->defaultValue(null),
            'si_col' => $this->smallInteger()->null()->defaultValue(null),
            'si_col_2' => 'smallint unsigned zerofill NULL DEFAULT NULL',
            'mi' => 'mediumint(10) unsigned zerofill comment \'comment\' NULL DEFAULT 7',
            'bi' => $this->bigInteger()->null()->defaultValue(null),
            'int_col' => 'int NULL DEFAULT NULL',
            'int_col_2' => $this->integer()->null()->defaultValue(null),
            'numeric' => 'numeric NULL DEFAULT NULL',
            'float' => $this->float()->null()->defaultValue(null),
            'float_2' => 'float(10, 2) NULL DEFAULT NULL',
            'float_3' => $this->float()->null()->defaultValue(null),
            'double' => $this->double()->null()->defaultValue(null),
            'double_p' => 'double precision(10,2) NULL DEFAULT NULL',
            'real' => 'real NULL DEFAULT NULL',
            'date' => $this->date()->null()->defaultValue(null),
            'time' => $this->time()->null()->defaultValue(null),
            'datetime' => $this->datetime()->null()->defaultValue(null),
            'timestamp' => $this->timestamp()->null()->defaultValue(null),
            'year' => 'year NULL DEFAULT NULL',
            'json' => 'json NOT NULL',
            'blob_def' => 'blob NULL',
            'text_def' => $this->text()->null(),
            'json_def' => 'json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%alldbdatatypes}}');
    }
}
