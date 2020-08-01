<?php

/**
 * Table for Post
 */
class m200000_000002_create_table_blog_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%blog_posts}}', [
            'uid' => $this->bigPrimaryKey(),
            'title' => $this->string(255)->notNull()->unique(),
            'slug' => $this->string(200)->null()->defaultValue(null)->unique(),
            'category_id' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->date()->null()->defaultValue(null),
            'created_by_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_blog_posts_category_id_categories_id', '{{%blog_posts}}', 'category_id', '{{%categories}}', 'id');
        $this->addForeignKey('fk_blog_posts_created_by_id_users_id', '{{%blog_posts}}', 'created_by_id', '{{%users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_blog_posts_created_by_id_users_id', '{{%blog_posts}}');
        $this->dropForeignKey('fk_blog_posts_category_id_categories_id', '{{%blog_posts}}');
        $this->dropTable('{{%blog_posts}}');
    }
}
