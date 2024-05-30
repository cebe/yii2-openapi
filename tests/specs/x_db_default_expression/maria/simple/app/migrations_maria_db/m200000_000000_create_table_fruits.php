<?php

/**
 * Table for Fruit
 */
class m200000_000000_create_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%fruits}}', [
            'ts' => $this->timestamp()->null()->defaultExpression("(CURRENT_TIMESTAMP)"),
            'ts2' => $this->timestamp()->null()->defaultValue('2011-11-11 00:00:00'),
            'ts3' => $this->timestamp()->null()->defaultValue('2022-11-11 00:00:00'),
            0 => 'ts4 timestamp NULL DEFAULT \'2022-11-11 00:00:00\'',
            1 => 'ts5 timestamp NULL DEFAULT (CURRENT_TIMESTAMP)',
            2 => 'ts6 timestamp NULL DEFAULT \'2000-11-11 00:00:00\'',
            3 => 'd date NULL DEFAULT (CURRENT_DATE + INTERVAL 1 YEAR)',
            4 => 'd2 text NULL DEFAULT (CURRENT_DATE + INTERVAL 1 YEAR)',
            5 => 'd3 text NULL DEFAULT \'text default\'',
            'ts7' => $this->date()->null()->defaultExpression("(CURRENT_DATE + INTERVAL 1 YEAR)"),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%fruits}}');
    }
}
