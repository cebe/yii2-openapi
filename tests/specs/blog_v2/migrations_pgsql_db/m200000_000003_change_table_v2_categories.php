<?php

/**
 * Table for Category
 */
class m200000_000003_change_table_v2_categories extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%v2_categories}}', 'cover', $this->text()->notNull());
        $this->alterColumn('{{%v2_categories}}', 'active', "DROP DEFAULT");
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(100)->notNull());
        $this->dropIndex('v2_categories_title_key', '{{%v2_categories}}');
        $this->createIndex('v2_categories_title_index', '{{%v2_categories}}', 'title', false);
    }

    public function safeDown()
    {
        $this->dropIndex('v2_categories_title_index', '{{%v2_categories}}');
        $this->createIndex('v2_categories_title_key', '{{%v2_categories}}', 'title', true);
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(255)->notNull());
        $this->dropColumn('{{%v2_categories}}', 'cover');
        $this->alterColumn('{{%v2_categories}}', 'active', "SET DEFAULT 'f'");
    }
}
