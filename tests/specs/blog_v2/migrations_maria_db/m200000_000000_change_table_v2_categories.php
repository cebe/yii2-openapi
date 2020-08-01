<?php

/**
 * Table for Category
 */
class m200000_000000_change_table_v2_categories extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_categories}}', 'cover', $this->text()->notNull());
        $this->alterColumn('{{%v2_categories}}', 'active', $this->boolean()->notNull());
        $this->createIndex('unique_title', '{{%v2_categories}}', 'title', true);
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(100)->notNull());
    }

    public function down()
    {
        $this->alterColumn('{{%v2_categories}}', 'title', $this->string(255)->notNull());
        $this->dropIndex('unique_title', '{{%v2_categories}}');
        $this->alterColumn('{{%v2_categories}}', 'active', $this->tinyInteger(1)->notNull()->defaultValue(0));
        $this->dropColumn('{{%v2_categories}}', 'cover');
    }
}
