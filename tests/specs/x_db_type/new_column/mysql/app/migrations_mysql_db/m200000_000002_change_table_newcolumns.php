<?php

/**
 * Table for Newcolumn
 */
class m200000_000002_change_table_newcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand("ALTER TABLE {{%newcolumns}} ADD COLUMN dec_col decimal(12,4) NULL DEFAULT NULL")->execute();
        $this->db->createCommand("ALTER TABLE {{%newcolumns}} ADD COLUMN json_col json NOT NULL")->execute();
        $this->addColumn('{{%newcolumns}}', 'last_name', $this->text()->null());
        $this->db->createCommand("ALTER TABLE {{%newcolumns}} ADD COLUMN numeric_col double precision NULL DEFAULT NULL")->execute();
        $this->db->createCommand("ALTER TABLE {{%newcolumns}} ADD COLUMN varchar_col varchar(5) NULL DEFAULT NULL")->execute();
    }

    public function down()
    {
        $this->dropColumn('{{%newcolumns}}', 'varchar_col');
        $this->dropColumn('{{%newcolumns}}', 'numeric_col');
        $this->dropColumn('{{%newcolumns}}', 'last_name');
        $this->dropColumn('{{%newcolumns}}', 'json_col');
        $this->dropColumn('{{%newcolumns}}', 'dec_col');
    }
}
