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
            'title' => $this->string(255)->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(false),
        ]);
        $this->createIndex('categories_active_index', '{{%categories}}', 'active', false);
        $this->createIndex('categories_title_key', '{{%categories}}', 'title', true);
    }

    public function down()
    {
        $this->dropIndex('categories_title_key', '{{%categories}}');
        $this->dropIndex('categories_active_index', '{{%categories}}');
        $this->dropTable('{{%categories}}');
    }
}
