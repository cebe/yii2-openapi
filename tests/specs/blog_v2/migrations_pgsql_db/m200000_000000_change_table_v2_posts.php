<?php

/**
 * Table for Post
 */
class m200000_000000_change_table_v2_posts extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%v2_posts}}', 'id', $this->bigPrimaryKey());
        $this->execute('CREATE TYPE enum_lang AS ENUM(\'ru\', \'eng\')');
        $this->addColumn('{{%v2_posts}}', 'lang', 'enum_lang NULL DEFAULT \'ru\'');
        $this->dropColumn('{{%v2_posts}}', 'uid');
        $this->alterColumn('{{%v2_posts}}', 'active', "DROP DEFAULT");
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->bigInteger()->notNull());
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->bigInteger()->null());
        $this->dropIndex('v2_posts_slug_key', '{{%v2_posts}}');
    }

    public function safeDown()
    {
        $this->createIndex('v2_posts_slug_key', '{{%v2_posts}}', 'slug', true);
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->integer()->null());
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->integer()->notNull());
        $this->addColumn('{{%v2_posts}}', 'uid', $this->bigInteger()->notNull());
        $this->dropColumn('{{%v2_posts}}', 'lang');
        $this->execute('DROP TYPE enum_lang');
        $this->dropColumn('{{%v2_posts}}', 'id');
        $this->alterColumn('{{%v2_posts}}', 'active', "SET DEFAULT 'f'");
    }
}
