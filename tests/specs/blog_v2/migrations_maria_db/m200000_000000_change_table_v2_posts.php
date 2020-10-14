<?php

/**
 * Table for Post
 */
class m200000_000000_change_table_v2_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_posts}}', 'id', $this->bigPrimaryKey());
        $this->addColumn('{{%v2_posts}}', 'lang', "enum('ru', 'eng') NULL DEFAULT 'ru'");
        $this->dropColumn('{{%v2_posts}}', 'uid');
        $this->alterColumn('{{%v2_posts}}', 'active', $this->tinyInteger(1)->notNull());
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->bigInteger()->notNull());
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->bigInteger()->null()->defaultValue(null));
    }

    public function down()
    {
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->integer(11)->null()->defaultValue(null));
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->integer(11)->notNull());
        $this->alterColumn('{{%v2_posts}}', 'active', $this->tinyInteger(1)->notNull()->defaultValue(0));
        $this->addColumn('{{%v2_posts}}', 'uid', $this->bigInteger(20)->notNull());
        $this->dropColumn('{{%v2_posts}}', 'lang');
        $this->dropColumn('{{%v2_posts}}', 'id');
    }
}
