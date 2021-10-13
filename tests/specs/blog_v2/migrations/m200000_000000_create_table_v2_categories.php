<?php

/**
 * Table for Category
 */
class m200000_000000_create_table_v2_categories extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%v2_categories}}', [
            'id' => $this->bigPrimaryKey(),
            'title' => $this->string(100)->notNull(),
            'cover' => $this->text()->notNull(),
            'active' => $this->boolean()->notNull(),
        ]);
        $this->createIndex('v2_categories_title_index', '{{%v2_categories}}', 'title', false);
    }

    public function down()
    {
        $this->dropIndex('v2_categories_title_index', '{{%v2_categories}}');
        $this->dropTable('{{%v2_categories}}');
    }
}
