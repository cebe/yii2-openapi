<?php

/**
 * Table for Comment
 */
class m200000_000003_change_table_v2_comments extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%v2_comments}}', 'user_id', $this->bigInteger()->null()->defaultValue(null));
        $this->dropColumn('{{%v2_comments}}', 'author_id');
        $this->alterColumn('{{%v2_comments}}', 'created_at', $this->timestamp()->notNull());
        $this->alterColumn('{{%v2_comments}}', 'message', $this->text()->notNull());
        $this->alterColumn('{{%v2_comments}}', 'post_id', $this->bigInteger()->notNull());
        $this->addForeignKey('fk_v2_comments_post_id_v2_posts_id', '{{%v2_comments}}', 'post_id', '{{%v2_posts}}', 'id');
        $this->addForeignKey('fk_v2_comments_user_id_v2_users_id', '{{%v2_comments}}', 'user_id', '{{%v2_users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_v2_comments_user_id_v2_users_id', '{{%v2_comments}}');
        $this->dropForeignKey('fk_v2_comments_post_id_v2_posts_id', '{{%v2_comments}}');
        $this->alterColumn('{{%v2_comments}}', 'post_id', $this->bigInteger(20)->notNull());
        $this->alterColumn('{{%v2_comments}}', 'message', "json NOT NULL");
        $this->alterColumn('{{%v2_comments}}', 'created_at', $this->integer(11)->notNull());
        $this->addColumn('{{%v2_comments}}', 'author_id', $this->integer(11)->notNull());
        $this->dropColumn('{{%v2_comments}}', 'user_id');
    }
}