<?php

/**
 * Table for Fruit
 */
class m200000_000005_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%fruits}}', 'name', $this->integer()->notNull()->first());
    }

    public function down()
    {
        $this->dropColumn('{{%fruits}}', 'name');
    }
}
