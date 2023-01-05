<?php

/**
 * Table for Pristine
 */
class m200000_000002_create_table_pristines extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%pristines}}', [
            'id' => $this->primaryKey(),
            'device' => 'enum("MOBILE", "TV", "COMPUTER") NOT NULL DEFAULT \'TV\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pristines}}');
    }
}
