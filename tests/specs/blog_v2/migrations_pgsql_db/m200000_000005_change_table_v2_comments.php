<?php

/**
 * Table for Comment
 */
class m200000_000005_change_table_v2_comments extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('fk_v2_comments_author_id_v2_users_id', '{{%v2_comments}}');
        $this->dropForeignKey('fk_v2_comments_post_id_v2_posts_uid', '{{%v2_comments}}');
        $this->addColumn('{{%v2_comments}}', 'user_id', $this->bigInteger()->null()->defaultValue(null));
        $this->dropColumn('{{%v2_comments}}', 'author_id');
        $this->alterColumn('{{%v2_comments}}', 'message', 'text NOT NULL USING "message"::text');
        $this->alterColumn('{{%v2_comments}}', 'message', "DROP DEFAULT");
        $this->alterColumn('{{%v2_comments}}', 'meta_data', 'varchar(300) NULL USING "meta_data"::varchar');
        $this->alterColumn('{{%v2_comments}}', 'meta_data', "DROP NOT NULL");
        $this->alterColumn('{{%v2_comments}}', 'meta_data', "SET DEFAULT ''");
        $this->alterColumn('{{%v2_comments}}', 'created_at', 'timestamp NOT NULL USING "created_at"::timestamp');
        $this->addForeignKey('fk_v2_comments_post_id_v2_posts_id', '{{%v2_comments}}', 'post_id', '{{%v2_posts}}', 'id');
        $this->addForeignKey('fk_v2_comments_user_id_v2_users_id', '{{%v2_comments}}', 'user_id', '{{%v2_users}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_v2_comments_user_id_v2_users_id', '{{%v2_comments}}');
        $this->dropForeignKey('fk_v2_comments_post_id_v2_posts_id', '{{%v2_comments}}');
        $this->alterColumn('{{%v2_comments}}', 'created_at', 'int4 NOT NULL USING "created_at"::int4');
        $this->alterColumn('{{%v2_comments}}', 'meta_data', 'jsonb NOT NULL USING "meta_data"::jsonb');
        $this->alterColumn('{{%v2_comments}}', 'message', 'jsonb NOT NULL USING "message"::jsonb');
        $this->addColumn('{{%v2_comments}}', 'author_id', $this->integer()->notNull());
        $this->dropColumn('{{%v2_comments}}', 'user_id');
        $this->alterColumn('{{%v2_comments}}', 'message', "SET DEFAULT '[]'");
        $this->alterColumn('{{%v2_comments}}', 'meta_data', "SET NOT NULL");
        $this->alterColumn('{{%v2_comments}}', 'meta_data', "SET DEFAULT '[]'");
        $this->addForeignKey('fk_v2_comments_post_id_v2_posts_uid', '{{%v2_comments}}', 'uid', 'v2_posts', 'post_id');
        $this->addForeignKey('fk_v2_comments_author_id_v2_users_id', '{{%v2_comments}}', 'id', 'v2_users', 'author_id');
    }
}
