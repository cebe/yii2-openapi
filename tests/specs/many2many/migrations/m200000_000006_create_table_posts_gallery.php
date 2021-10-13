<?php

/**
 * Table for PostsGallery
 */
class m200000_000006_create_table_posts_gallery extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%posts_gallery}}', [
            'image_id' => $this->bigInteger()->null()->defaultValue(null),
            'article_id' => $this->bigInteger()->null()->defaultValue(null),
            'is_cover' => $this->text()->null()->defaultValue(null),
        ]);
        $this->addPrimaryKey('pk_image_id_article_id', '{{%posts_gallery}}', 'image_id,article_id');
        $this->addForeignKey('fk_posts_gallery_image_id_photo_id', '{{%posts_gallery}}', 'image_id', '{{%photo}}', 'id');
        $this->addForeignKey('fk_posts_gallery_article_id_posts_id', '{{%posts_gallery}}', 'article_id', '{{%posts}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_posts_gallery_article_id_posts_id', '{{%posts_gallery}}');
        $this->dropForeignKey('fk_posts_gallery_image_id_photo_id', '{{%posts_gallery}}');
        $this->dropPrimaryKey('pk_image_id_article_id', '{{%posts_gallery}}');
        $this->dropTable('{{%posts_gallery}}');
    }
}
