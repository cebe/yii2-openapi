<?php

/**
 * Table for Addtwonewcolinbetween2s
 */
class m200000_000000_change_table_addtwonewcolinbetween2s extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%addtwonewcolinbetween2s}} ADD COLUMN password text NULL DEFAULT NULL AFTER name')->execute();
        $this->db->createCommand('ALTER TABLE {{%addtwonewcolinbetween2s}} ADD COLUMN screen_name text NULL DEFAULT NULL AFTER last_name')->execute();
        $this->db->createCommand('ALTER TABLE {{%addtwonewcolinbetween2s}} ADD COLUMN nick_name text NULL DEFAULT NULL AFTER screen_name')->execute();
    }

    public function down()
    {
        $this->dropColumn('{{%addtwonewcolinbetween2s}}', 'nick_name');
        $this->dropColumn('{{%addtwonewcolinbetween2s}}', 'screen_name');
        $this->dropColumn('{{%addtwonewcolinbetween2s}}', 'password');
    }
}
