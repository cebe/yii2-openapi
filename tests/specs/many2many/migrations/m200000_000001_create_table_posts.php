<?php

/**
 * Table for Post
 */
class m200000_000001_create_table_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->bigPrimaryKey(),
            'title' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%posts}}');
    }
}
