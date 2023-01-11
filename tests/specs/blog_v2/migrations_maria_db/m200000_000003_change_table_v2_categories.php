<?php

/**
 * Table for Category
 */
class m200000_000003_change_table_v2_categories extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_categories}}', 'cover', $this->text()->notNull()->after('title'));
        $this->alterColumn('{{%v2_categories}}', 'active', $this->tinyInteger(1)->notNull());
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(100)->notNull());
        $this->dropIndex('v2_categories_title_key', '{{%v2_categories}}');
        $this->createIndex('v2_categories_title_index', '{{%v2_categories}}', 'title', false);
    }

    public function down()
    {
        $this->dropIndex('v2_categories_title_index', '{{%v2_categories}}');
        $this->createIndex('v2_categories_title_key', '{{%v2_categories}}', 'title', true);
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(255)->notNull());
        $this->alterColumn('{{%v2_categories}}', 'active', $this->tinyInteger(1)->notNull()->defaultValue(0));
        $this->dropColumn('{{%v2_categories}}', 'cover');
    }
}
