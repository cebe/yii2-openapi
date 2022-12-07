<?php

/**
 * Table for Pristine
 */
class m200000_000001_create_table_pristines extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%pristines}}', [
            'custom_id_col' => 'integer primary key auto_increment NOT NULL',
            'name' => $this->text()->notNull(),
            'tag' => $this->text()->null(),
            'new_col' => $this->string()->null()->defaultValue(null),
            'col_5' => 'decimal(12,4) NULL DEFAULT NULL',
            'col_6' => 'decimal(11,2) NULL DEFAULT NULL',
            'col_7' => 'decimal(10,2) NULL DEFAULT NULL',
            'col_8' => 'json NOT NULL',
            'col_9' => $this->string()->null()->defaultValue(null),
            'col_10' => $this->string()->null()->defaultValue(null),
            'col_11' => $this->text()->null(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pristines}}');
    }
}
