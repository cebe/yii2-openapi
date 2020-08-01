<?php

/**
 * Table for Tag
 */
class m200000_000004_create_table_v2_tags extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%v2_tags}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'lang' => 'enum(\'ru\', \'eng\') NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%v2_tags}}');
    }
}
