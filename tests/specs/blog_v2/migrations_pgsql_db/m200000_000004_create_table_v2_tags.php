<?php

/**
 * Table for Tag
 */
class m200000_000004_create_table_v2_tags extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_lang AS ENUM(\'ru\',\'eng\')');
        $this->createTable('{{%v2_tags}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'lang' => 'enum_lang NOT NULL',
        ]);
    }

    public function safeDown()
    {
        $this->execute('DROP TYPE enum_lang');
        $this->dropTable('{{%v2_tags}}');
    }
}
