<?php

/**
 * Table for Post
 */
class m200000_000002_create_table_v2_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%v2_posts}}', [
            'id' => $this->bigPrimaryKey(),
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(200)->null()->defaultValue(null),
            'lang' => 'enum(\'ru\', \'eng\') NULL DEFAULT \'ru\'',
            'category_id' => $this->bigInteger()->notNull(),
            'active' => $this->boolean()->notNull(),
            'created_at' => $this->date()->null()->defaultValue(null),
            'created_by_id' => $this->bigInteger()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}', 'category_id', '{{%v2_categories}}', 'id');
        $this->addForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}', 'created_by_id', '{{%v2_users}}', 'id');
        $this->createIndex('v2_posts_title_key', '{{%v2_posts}}', 'title', true);
    }

    public function down()
    {
        $this->dropIndex('v2_posts_title_key', '{{%v2_posts}}');
        $this->dropForeignKey('fk_v2_posts_created_by_id_v2_users_id', '{{%v2_posts}}');
        $this->dropForeignKey('fk_v2_posts_category_id_v2_categories_id', '{{%v2_posts}}');
        $this->dropTable('{{%v2_posts}}');
    }
}
