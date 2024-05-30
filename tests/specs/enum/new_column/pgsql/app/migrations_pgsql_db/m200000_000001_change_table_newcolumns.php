<?php

/**
 * Table for Newcolumn
 */
class m200000_000001_change_table_newcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE "enum_itt_newcolumns_new_column" AS ENUM(\'ONE\', \'TWO\', \'THREE\')');
        $this->addColumn('{{%newcolumns}}', 'new_column', '"enum_itt_newcolumns_new_column" NOT NULL DEFAULT \'ONE\'');
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "new_column_x" varchar(10) NOT NULL DEFAULT \'ONE\'')->execute();
        $this->dropColumn('{{%newcolumns}}', 'delete_col');
        $this->execute('DROP TYPE "enum_itt_newcolumns_delete_col"');
    }

    public function safeDown()
    {
        $this->execute('CREATE TYPE "enum_itt_newcolumns_delete_col" AS ENUM(\'FOUR\', \'FIVE\', \'SIX\')');
        $this->addColumn('{{%newcolumns}}', 'delete_col', '"enum_itt_newcolumns_delete_col" NULL DEFAULT NULL');
        $this->dropColumn('{{%newcolumns}}', 'new_column_x');
        $this->dropColumn('{{%newcolumns}}', 'new_column');
        $this->execute('DROP TYPE "enum_itt_newcolumns_new_column"');
    }
}
