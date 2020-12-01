<?php

/**
 * Table for Photos2Posts
 */
class m200000_000002_create_table_photos2posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%photos2posts}}', [
            'id' => $this->bigPrimaryKey(),
            'photo_id' => $this->bigInteger()->null()->defaultValue(null),
            'post_id' => $this->bigInteger()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_photos2posts_photo_id_photo_id', '{{%photos2posts}}', 'photo_id', '{{%photo}}', 'id');
        $this->addForeignKey('fk_photos2posts_post_id_posts_id', '{{%photos2posts}}', 'post_id', '{{%posts}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_photos2posts_post_id_posts_id', '{{%photos2posts}}');
        $this->dropForeignKey('fk_photos2posts_photo_id_photo_id', '{{%photos2posts}}');
        $this->dropTable('{{%photos2posts}}');
    }
}
