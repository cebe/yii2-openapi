<?php

/**
 * Table for Post
 */
class m200000_000001_create_table_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->null(),
            'user_id' => $this->integer()->null()->defaultValue(null),
            'user_2_id' => $this->integer()->null()->defaultValue(null),
            'user_3_id' => $this->integer()->null()->defaultValue(null),
            'user_4_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_posts_user_id_users_id', '{{%posts}}', 'user_id', '{{%users}}', 'id', null, 'CASCADE');
        $this->addForeignKey('fk_posts_user_2_id_users_id', '{{%posts}}', 'user_2_id', '{{%users}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_posts_user_3_id_users_id', '{{%posts}}', 'user_3_id', '{{%users}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_posts_user_4_id_users_id', '{{%posts}}', 'user_4_id', '{{%users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_posts_user_4_id_users_id', '{{%posts}}');
        $this->dropForeignKey('fk_posts_user_3_id_users_id', '{{%posts}}');
        $this->dropForeignKey('fk_posts_user_2_id_users_id', '{{%posts}}');
        $this->dropForeignKey('fk_posts_user_id_users_id', '{{%posts}}');
        $this->dropTable('{{%posts}}');
    }
}
