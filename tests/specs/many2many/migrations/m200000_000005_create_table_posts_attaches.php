<?php

/**
 * Table for PostsAttaches
 */
class m200000_000005_create_table_posts_attaches extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%posts_attaches}}', [
            'id' => $this->bigPrimaryKey(),
            'attach_id' => $this->bigInteger()->null()->defaultValue(null),
            'target_id' => $this->bigInteger()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_posts_attaches_attach_id_photo_id', '{{%posts_attaches}}', 'attach_id', '{{%photo}}', 'id');
        $this->addForeignKey('fk_posts_attaches_target_id_posts_id', '{{%posts_attaches}}', 'target_id', '{{%posts}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_posts_attaches_target_id_posts_id', '{{%posts_attaches}}');
        $this->dropForeignKey('fk_posts_attaches_attach_id_photo_id', '{{%posts_attaches}}');
        $this->dropTable('{{%posts_attaches}}');
    }
}
