<?php

/**
 * Table for Twocol2
 */
class m200000_000006_change_table_twocol2s extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%twocol2s}} ADD COLUMN email text NULL FIRST')->execute();
        $this->db->createCommand('ALTER TABLE {{%twocol2s}} ADD COLUMN last_name text NULL AFTER email')->execute();
    }

    public function down()
    {
        $this->dropColumn('{{%twocol2s}}', 'last_name');
        $this->dropColumn('{{%twocol2s}}', 'email');
    }
}
