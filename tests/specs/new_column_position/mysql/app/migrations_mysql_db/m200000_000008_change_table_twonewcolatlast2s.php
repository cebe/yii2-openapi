<?php

/**
 * Table for Twonewcolatlast2
 */
class m200000_000008_change_table_twonewcolatlast2s extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%twonewcolatlast2s}} ADD COLUMN name text NULL AFTER email')->execute();
        $this->db->createCommand('ALTER TABLE {{%twonewcolatlast2s}} ADD COLUMN last_name text NULL')->execute();
    }

    public function down()
    {
        $this->dropColumn('{{%twonewcolatlast2s}}', 'last_name');
        $this->dropColumn('{{%twonewcolatlast2s}}', 'name');
    }
}
