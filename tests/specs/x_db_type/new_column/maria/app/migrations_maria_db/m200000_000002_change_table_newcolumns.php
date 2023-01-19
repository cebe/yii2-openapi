<?php

/**
 * Table for Newcolumn
 */
class m200000_000002_change_table_newcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%newcolumns}}', 'last_name', $this->text()->null()->defaultValue(null)->after('name'));
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN dec_col decimal(12,4) NULL DEFAULT NULL AFTER last_name')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN json_col json NOT NULL AFTER dec_col')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN varchar_col varchar(5) NULL DEFAULT NULL AFTER json_col')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN numeric_col double precision NULL DEFAULT NULL AFTER varchar_col')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN json_col_def_n json NOT NULL DEFAULT \'[]\'')->execute();
    }

    public function down()
    {
        $this->dropColumn('{{%newcolumns}}', 'json_col_def_n');
        $this->dropColumn('{{%newcolumns}}', 'numeric_col');
        $this->dropColumn('{{%newcolumns}}', 'varchar_col');
        $this->dropColumn('{{%newcolumns}}', 'json_col');
        $this->dropColumn('{{%newcolumns}}', 'dec_col');
        $this->dropColumn('{{%newcolumns}}', 'last_name');
    }
}
