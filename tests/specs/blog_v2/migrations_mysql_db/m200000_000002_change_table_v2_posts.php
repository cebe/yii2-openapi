<?php

/**
 * Table for Post
 */
class m200000_000002_change_table_v2_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_posts}}', 'id', $this->bigPrimaryKey());
        $this->addColumn('{{%v2_posts}}', 'lang', "enum('ru', 'eng') NULL DEFAULT 'ru'");
        $this->dropColumn('{{%v2_posts}}', 'uid');
        $this->alterColumn('{{%v2_posts}}', 'active', $this->boolean()->notNull());
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->bigInteger()->notNull());
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->bigInteger()->null()->defaultValue(null));
        $this->createIndex('unique_title', '{{%v2_posts}}', 'title', true);
        $this->addForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}', 'category_id', '{{%v2_categories}}', 'id');
        $this->addForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}', 'created_by_id', '{{%v2_users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}');
        $this->dropForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}');
        $this->dropIndex('unique_title', '{{%v2_posts}}');
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->integer(11)->null()->defaultValue(null));
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->integer(11)->notNull());
        $this->alterColumn('{{%v2_posts}}', 'active', $this->tinyInteger(1)->notNull()->defaultValue(0));
        $this->addColumn('{{%v2_posts}}', 'uid', $this->bigInteger(20)->notNull());
        $this->dropColumn('{{%v2_posts}}', 'lang');
        $this->dropColumn('{{%v2_posts}}', 'id');
    }
}
