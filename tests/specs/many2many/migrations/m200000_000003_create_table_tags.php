<?php

/**
 * Table for Tag
 */
class m200000_000003_create_table_tags extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%tags}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%tags}}');
    }
}
