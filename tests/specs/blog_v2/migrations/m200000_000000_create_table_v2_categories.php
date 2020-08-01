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
            'title' => $this->string(100)->notNull()->unique(),
            'cover' => $this->text()->notNull(),
            'active' => $this->boolean()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%v2_categories}}');
    }
}
