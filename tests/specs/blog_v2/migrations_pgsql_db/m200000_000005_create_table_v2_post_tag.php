<?php

/**
 * Table for PostTag
 */
class m200000_000005_create_table_v2_post_tag extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%v2_post_tag}}', [
            'id' => $this->bigPrimaryKey(),
            'post_id' => $this->bigInteger()->notNull(),
            'tag_id' => $this->bigInteger()->notNull(),
        ]);
        $this->addForeignKey('fk_v2_post_tag_post_id_v2_posts_id', '{{%v2_post_tag}}', 'post_id', '{{%v2_posts}}', 'id');
        $this->addForeignKey('fk_v2_post_tag_tag_id_v2_tags_id', '{{%v2_post_tag}}', 'tag_id', '{{%v2_tags}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_v2_post_tag_tag_id_v2_tags_id', '{{%v2_post_tag}}');
        $this->dropForeignKey('fk_v2_post_tag_post_id_v2_posts_id', '{{%v2_post_tag}}');
        $this->dropTable('{{%v2_post_tag}}');
    }
}
