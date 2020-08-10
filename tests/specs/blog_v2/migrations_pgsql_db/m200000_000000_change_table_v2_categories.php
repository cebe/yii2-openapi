<?php

/**
 * Table for Category
 */
class m200000_000000_change_table_v2_categories extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%v2_categories}}', 'cover', $this->text()->notNull());
        $this->alterColumn('{{%v2_categories}}', 'active', "DROP DEFAULT");
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(100));
        $this->createIndex('unique_title', '{{%v2_categories}}', 'title', true);
    }

    public function safeDown()
    {
        $this->dropIndex('unique_title', '{{%v2_categories}}');
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(255));
        $this->dropColumn('{{%v2_categories}}', 'cover');
        $this->alterColumn('{{%v2_categories}}', 'active', "SET DEFAULT 'f'");
    }
}
