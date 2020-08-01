<?php

/**
 * Table for Post
 */
class m200000_000002_change_table_v2_posts extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_lang AS ENUM(\'ru\',\'eng\')');
        $this->addColumn('{{%v2_posts}}', 'id', $this->bigPrimaryKey());
        $this->addColumn('{{%v2_posts}}', 'lang', "enum_lang NULL DEFAULT 'ru'");
        $this->dropColumn('{{%v2_posts}}', 'uid');
        $this->alterColumn('{{%v2_posts}}', 'active', "DROP DEFAULT");
        $this->alterColumn('{{%v2_posts}}', 'category', $this->bigInteger());
        $this->alterColumn('{{%v2_posts}}', 'created_by', $this->bigInteger());
        $this->createIndex('unique_title', '{{%v2_posts}}', 'title', true);
        $this->addForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}', 'category_id', '{{%v2_categories}}', 'id');
        $this->addForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}', 'created_by_id', '{{%v2_users}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}');
        $this->dropForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}');
        $this->dropIndex('unique_title', '{{%v2_posts}}');
        $this->addColumn('{{%v2_posts}}', 'uid', $this->bigInteger()->notNull());
        $this->execute('DROP TYPE enum_lang');
        $this->dropColumn('{{%v2_posts}}', 'lang');
        $this->dropColumn('{{%v2_posts}}', 'id');
        $this->alterColumn('{{%v2_posts}}', 'active', "SET DEFAULT FALSE");
        $this->alterColumn('{{%v2_posts}}', 'category', $this->integer());
        $this->alterColumn('{{%v2_posts}}', 'created_by', $this->integer());
    }
}
