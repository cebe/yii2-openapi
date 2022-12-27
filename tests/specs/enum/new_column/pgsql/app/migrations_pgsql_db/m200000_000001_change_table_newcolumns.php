<?php

/**
 * Table for Newcolumn
 */
class m200000_000001_change_table_newcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_new_column AS ENUM(\'ONE\', \'TWO\', \'THREE\')');
        $this->addColumn('{{%newcolumns}}', 'new_column', 'enum_new_column NOT NULL DEFAULT \'ONE\'');
        $this->dropColumn('{{%newcolumns}}', 'delete_col');
        $this->execute('DROP TYPE enum_delete_col');
    }

    public function safeDown()
    {
        $this->execute('CREATE TYPE enum_delete_col AS ENUM(\'FOUR\', \'FIVE\', \'SIX\')');
        $this->addColumn('{{%newcolumns}}', 'delete_col', 'enum_delete_col NULL DEFAULT NULL');
        $this->dropColumn('{{%newcolumns}}', 'new_column');
        $this->execute('DROP TYPE enum_new_column');
    }
}
