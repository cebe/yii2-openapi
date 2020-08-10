<?php

/**
 * Table for Custom
 */
class m200000_000000_change_table_v3_pgcustom extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_status AS ENUM(\'draft\',\'pending\',\'active\')');
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', 'SET DEFAULT \'[]\'');
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', 'SET DEFAULT \'[]\'');
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', 'SET DEFAULT \'[{"foo":"foobar"},{"xxx":"yyy"}]\'');
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', 'SET DEFAULT \'{"foo":"bar","bar":"baz"}\'');
        $this->execute('DROP TYPE enum_status');
        $this->alterColumn('{{%v3_pgcustom}}', 'status', "enum_status USING status::enum_status");
        $this->alterColumn('{{%v3_pgcustom}}', 'status', "SET DEFAULT 'draft'");
    }

    public function safeDown()
    {
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "DROP DEFAULT");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "DROP DEFAULT");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', 'SET DEFAULT \'{"bar":"baz","foo":"bar"}\'');
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', 'SET DEFAULT \'{"ffo":"bar"}\'');
        $this->alterColumn('{{%v3_pgcustom}}', 'status', $this->string());
        $this->alterColumn('{{%v3_pgcustom}}', 'status', "DROP DEFAULT");
        $this->execute('CREATE TYPE enum_status AS ENUM(\'active\',\'draft\')');
        $this->execute('DROP TYPE enum_status');
    }
}
