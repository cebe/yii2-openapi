<?php

/**
 * Table for Posts2Tags
 */
class m200000_000002_create_table_posts2tags extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%posts2tags}}', [
            'post_id' => $this->bigInteger()->notNull(),
            'tag_id' => $this->bigInteger()->notNull(),
        ]);
        $this->addPrimaryKey('pk_post_id_tag_id', '{{%posts2tags}}', 'post_id,tag_id');
        $this->addForeignKey('fk_posts2tags_post_id_v2_posts_id', '{{%posts2tags}}', 'post_id', '{{%v2_posts}}', 'id');
        $this->addForeignKey('fk_posts2tags_tag_id_v2_tags_id', '{{%posts2tags}}', 'tag_id', '{{%v2_tags}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_posts2tags_tag_id_v2_tags_id', '{{%posts2tags}}');
        $this->dropForeignKey('fk_posts2tags_post_id_v2_posts_id', '{{%posts2tags}}');
        $this->dropPrimaryKey('pk_post_id_tag_id', '{{%posts2tags}}');
        $this->dropTable('{{%posts2tags}}');
    }
}
