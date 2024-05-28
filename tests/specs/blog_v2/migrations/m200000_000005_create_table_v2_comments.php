<?php

/**
 * Table for Comment
 */
class m200000_000005_create_table_v2_comments extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%v2_comments}}', [
            'id' => $this->bigPrimaryKey(),
            'post_id' => $this->bigInteger()->notNull(),
            'user_id' => $this->bigInteger()->null()->defaultValue(null),
            'message' => $this->text()->notNull(),
            'meta_data' => $this->string(300)->null()->defaultValue(''),
            'created_at' => $this->timestamp()->notNull(),
        ]);
        $this->addForeignKey('fk_v2_comments_post_id_v2_posts_id', '{{%v2_comments}}', 'post_id', '{{%v2_posts}}', 'id');
        $this->addForeignKey('fk_v2_comments_user_id_v2_users_id', '{{%v2_comments}}', 'user_id', '{{%v2_users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_v2_comments_user_id_v2_users_id', '{{%v2_comments}}');
        $this->dropForeignKey('fk_v2_comments_post_id_v2_posts_id', '{{%v2_comments}}');
        $this->dropTable('{{%v2_comments}}');
    }
}
