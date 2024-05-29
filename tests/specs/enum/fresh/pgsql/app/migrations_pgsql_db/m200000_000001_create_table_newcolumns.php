<?php

/**
 * Table for Newcolumn
 */
class m200000_000001_create_table_newcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE "enum_itt_newcolumns_new_column" AS ENUM(\'ONE\', \'TWO\', \'THREE\')');
        $this->createTable('{{%newcolumns}}', [
            'id' => $this->primaryKey(),
            'new_column' => '"enum_itt_newcolumns_new_column" NOT NULL DEFAULT \'ONE\'',
            0 => '"new_column_x" varchar(10) NOT NULL DEFAULT \'ONE\'',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%newcolumns}}');
        $this->execute('DROP TYPE "enum_itt_newcolumns_new_column"');
    }
}
