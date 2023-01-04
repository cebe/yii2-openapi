<?php

/**
 * Table for Editcolumn
 */
class m200000_000000_create_table_editcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE "enum_itt_editcolumns_connection" AS ENUM(\'WIRED\', \'WIRELESS\')');
        $this->createTable('{{%editcolumns}}', [
            'id' => $this->primaryKey(),
            'device' => $this->text()->null()->defaultValue(null),
            'connection' => 'enum_itt_editcolumns_connection NOT NULL DEFAULT \'WIRED\'',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%editcolumns}}');
        $this->execute('DROP TYPE "enum_itt_editcolumns_connection"');
    }
}
