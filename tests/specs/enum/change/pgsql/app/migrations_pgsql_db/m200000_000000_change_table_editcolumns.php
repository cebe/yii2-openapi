<?php

/**
 * Table for Editcolumn
 */
class m200000_000000_change_table_editcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE "enum_itt_editcolumns_connection" AS ENUM(\'WIRED\', \'WIRELESS\')');
        $this->alterColumn('{{%editcolumns}}', 'connection', 'enum_itt_editcolumns_connection USING "connection"::"enum_itt_editcolumns_connection"');
        $this->alterColumn('{{%editcolumns}}', 'connection', "SET NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'connection', "SET DEFAULT 'WIRED'");
        $this->alterColumn('{{%editcolumns}}', 'device', 'text NULL USING "device"::text');
        $this->alterColumn('{{%editcolumns}}', 'device', "DROP NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'device', "DROP DEFAULT");
        $this->execute('DROP TYPE "enum_itt_editcolumns_device"');
    }

    public function safeDown()
    {
        $this->execute('CREATE TYPE "enum_itt_editcolumns_device" AS ENUM(\'MOBILE\', \'TV\', \'COMPUTER\')');
        $this->alterColumn('{{%editcolumns}}', 'device', 'enum_itt_editcolumns_device USING "device"::"enum_itt_editcolumns_device"');
        $this->alterColumn('{{%editcolumns}}', 'connection', 'varchar(255) NULL USING "connection"::varchar');
        $this->alterColumn('{{%editcolumns}}', 'connection', "DROP NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'connection', "DROP DEFAULT");
        $this->execute('DROP TYPE "enum_itt_editcolumns_connection"');
        $this->alterColumn('{{%editcolumns}}', 'device', "SET NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'device', "SET DEFAULT 'TV'");
    }
}
