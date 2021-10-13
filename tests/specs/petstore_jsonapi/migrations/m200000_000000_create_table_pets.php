<?php

/**
 * Table for Pet
 */
class m200000_000000_create_table_pets extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%pets}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->text()->notNull(),
            'tag' => $this->text()->null()->defaultValue(null),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pets}}');
    }
}
