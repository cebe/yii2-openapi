<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%fruits}}', 'colourName', 'text NULL USING "colourName"::text');
    }

    public function safeDown()
    {
        $this->alterColumn('{{%fruits}}', 'colourName', 'varchar(255) NULL USING "colourName"::varchar');
    }
}
