<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'ts', $this->timestamp()->null()->defaultExpression("(CURRENT_TIMESTAMP)"));
        $this->alterColumn('{{%fruits}}', 'ts2', $this->timestamp()->null()->defaultValue('2011-11-11 00:00:00'));
        $this->alterColumn('{{%fruits}}', 'ts3', $this->timestamp()->null()->defaultValue('2022-11-11 00:00:00'));
        $this->alterColumn('{{%fruits}}', 'ts4', $this->timestamp()->null()->defaultValue('2022-11-11 00:00:00'));
        $this->alterColumn('{{%fruits}}', 'ts5', $this->timestamp()->null()->defaultExpression("(CURRENT_TIMESTAMP)"));
        $this->alterColumn('{{%fruits}}', 'ts6', $this->timestamp()->null()->defaultValue('2000-11-11 00:00:00'));
        $this->alterColumn('{{%fruits}}', 'd', $this->date()->null()->defaultExpression("(CURRENT_DATE + INTERVAL 1 YEAR)"));
        $this->alterColumn('{{%fruits}}', 'd2', $this->text()->null()->defaultExpression("(CURRENT_DATE + INTERVAL 1 YEAR)"));
        $this->alterColumn('{{%fruits}}', 'd3', $this->text()->null()->defaultValue('text default'));
        $this->alterColumn('{{%fruits}}', 'ts7', $this->date()->null()->defaultExpression("(CURRENT_DATE + INTERVAL 1 YEAR)"));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'ts7', $this->date()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'd3', $this->text()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'd2', $this->text()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'd', $this->date()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'ts6', $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'));
        $this->alterColumn('{{%fruits}}', 'ts5', $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'));
        $this->alterColumn('{{%fruits}}', 'ts4', $this->timestamp()->notNull()->defaultExpression("current_timestamp()"));
        $this->alterColumn('{{%fruits}}', 'ts3', $this->datetime()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'ts2', $this->datetime()->null()->defaultValue(null));
        $this->alterColumn('{{%fruits}}', 'ts', $this->datetime()->null()->defaultValue(null));
    }
}
