<?php

/**
 * Table for Tag
 */
class m200000_000001_create_table_v2_tags extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_lang AS ENUM(\'ru\', \'eng\')');
        $this->createTable('{{%v2_tags}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(100)->notNull(),
            'lang' => 'enum_lang NOT NULL',
        ]);
        $this->createIndex('v2_tags_name_key', '{{%v2_tags}}', 'name', true);
    }

    public function safeDown()
    {
        $this->dropIndex('v2_tags_name_key', '{{%v2_tags}}');
        $this->execute('DROP TYPE enum_lang');
        $this->dropTable('{{%v2_tags}}');
    }
}
