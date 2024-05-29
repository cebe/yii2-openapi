<?php

/**
 * Table for Post
 */
class m200000_000002_create_table_blog_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%blog_posts}}', [
            0 => 'uid varchar(128) NOT NULL',
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(200)->null()->defaultValue(null),
            'category_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->date()->null()->defaultValue(null),
            'created_by_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addPrimaryKey('pk_blog_posts_uid', '{{%blog_posts}}', 'uid');
        $this->createIndex('blog_posts_title_key', '{{%blog_posts}}', 'title', true);
        $this->createIndex('blog_posts_slug_key', '{{%blog_posts}}', 'slug', true);
        $this->addForeignKey('fk_blog_posts_category_id_categories_id', '{{%blog_posts}}', 'category_id', '{{%categories}}', 'id');
        $this->addForeignKey('fk_blog_posts_created_by_id_users_id', '{{%blog_posts}}', 'created_by_id', '{{%users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_blog_posts_created_by_id_users_id', '{{%blog_posts}}');
        $this->dropForeignKey('fk_blog_posts_category_id_categories_id', '{{%blog_posts}}');
        $this->dropIndex('blog_posts_slug_key', '{{%blog_posts}}');
        $this->dropIndex('blog_posts_title_key', '{{%blog_posts}}');
        $this->dropPrimaryKey('pk_blog_posts_uid', '{{%blog_posts}}');
        $this->dropTable('{{%blog_posts}}');
    }
}
