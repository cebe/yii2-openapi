<?php

/**
 * Table for Newcolumn
 */
class m200000_000001_create_table_newcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%newcolumns}}', [
            'id' => $this->primaryKey(),
            'new_column' => 'enum("ONE", "TWO", "THREE") NOT NULL DEFAULT \'ONE\'',
            0 => 'new_column_x varchar(10) NOT NULL DEFAULT \'ONE\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%newcolumns}}');
    }
}
