<?php

/**
 * Table for Pristine
 */
class m200000_000002_create_table_pristines extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE "enum_device" AS ENUM(\'MOBILE\', \'TV\', \'COMPUTER\')');
        $this->createTable('{{%pristines}}', [
            'id' => $this->primaryKey(),
            'device' => 'enum_device NOT NULL DEFAULT \'TV\'',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%pristines}}');
        $this->execute('DROP TYPE "enum_device"');
    }
}
