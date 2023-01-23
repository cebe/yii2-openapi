<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%fruits}}', 'ts', "SET DEFAULT (CURRENT_TIMESTAMP)");
        $this->alterColumn('{{%fruits}}', 'ts2', "SET DEFAULT '2011-11-11 00:00:00'");
        $this->alterColumn('{{%fruits}}', 'ts3', "SET DEFAULT '2022-11-11 00:00:00'");
        $this->alterColumn('{{%fruits}}', 'ts4', "SET DEFAULT '2022-11-11 00:00:00'");
        $this->alterColumn('{{%fruits}}', 'ts5', "SET DEFAULT (CURRENT_TIMESTAMP)");
        $this->alterColumn('{{%fruits}}', 'ts6', "SET DEFAULT '2000-11-11 00:00:00'");
        $this->alterColumn('{{%fruits}}', 'd', "SET DEFAULT (CURRENT_DATE + INTERVAL '1 YEAR')");
        $this->alterColumn('{{%fruits}}', 'd2', "SET DEFAULT (CURRENT_DATE + INTERVAL '1 YEAR')");
        $this->alterColumn('{{%fruits}}', 'd3', "SET DEFAULT 'text default'");
        $this->alterColumn('{{%fruits}}', 'ts7', "SET DEFAULT (CURRENT_DATE + INTERVAL '1 YEAR')");
    }

    public function safeDown()
    {
        $this->alterColumn('{{%fruits}}', 'ts', "SET DEFAULT '2011-11-11 00:00:00'");
        $this->alterColumn('{{%fruits}}', 'ts2', "SET DEFAULT CURRENT_TIMESTAMP");
        $this->alterColumn('{{%fruits}}', 'ts3', "SET DEFAULT CURRENT_TIMESTAMP");
        $this->alterColumn('{{%fruits}}', 'ts4', "SET DEFAULT CURRENT_TIMESTAMP");
        $this->alterColumn('{{%fruits}}', 'ts5', "SET DEFAULT '2011-11-11 00:00:00'");
        $this->alterColumn('{{%fruits}}', 'ts6', "SET DEFAULT CURRENT_TIMESTAMP");
        $this->alterColumn('{{%fruits}}', 'd', "SET DEFAULT '2011-11-11'");
        $this->alterColumn('{{%fruits}}', 'd2', "DROP DEFAULT");
        $this->alterColumn('{{%fruits}}', 'd3', "DROP DEFAULT");
        $this->alterColumn('{{%fruits}}', 'ts7', "SET DEFAULT '2011-11-11'");
    }
}
