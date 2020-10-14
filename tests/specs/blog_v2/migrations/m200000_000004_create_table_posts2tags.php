<?php

/**
 * Table for Posts2Tags
 */
class m200000_000004_create_table_posts2tags extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%posts2tags}}', [
            'id' => $this->bigPrimaryKey(),
            'post_id' => $this->bigInteger()->notNull(),
            'tag_id' => $this->bigInteger()->notNull(),
        ]);
        $this->addForeignKey('fk_posts2tags_post_id_v2_posts_id', '{{%posts2tags}}', 'post_id', '{{%v2_posts}}', 'id');
        $this->addForeignKey('fk_posts2tags_tag_id_v2_tags_id', '{{%posts2tags}}', 'tag_id', '{{%v2_tags}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_posts2tags_tag_id_v2_tags_id', '{{%posts2tags}}');
        $this->dropForeignKey('fk_posts2tags_post_id_v2_posts_id', '{{%posts2tags}}');
        $this->dropTable('{{%posts2tags}}');
    }
}
