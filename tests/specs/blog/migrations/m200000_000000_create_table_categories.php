<?php

/**
 * Table for Category
 */
class m200000_000000_create_table_categories extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%categories}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull()->unique(),
            'active' => $this->boolean()->notNull()->defaultValue(false),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%categories}}');
    }
}
