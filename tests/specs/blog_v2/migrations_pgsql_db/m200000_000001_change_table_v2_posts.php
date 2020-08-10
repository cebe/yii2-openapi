<?php

/**
 * Table for Post
 */
class m200000_000001_change_table_v2_posts extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->execute('CREATE TYPE enum_lang AS ENUM(\'ru\', \'eng\')');
        $this->addColumn('{{%v2_posts}}', 'id', $this->bigPrimaryKey());
        $this->addColumn('{{%v2_posts}}', 'lang', 'enum_lang NULL DEFAULT \'ru\'');
        $this->dropColumn('{{%v2_posts}}', 'uid');
        $this->alterColumn('{{%v2_posts}}', 'active', "DROP DEFAULT");
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->bigInteger());
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->bigInteger());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', $this->integer());
        $this->alterColumn('{{%v2_posts}}', 'category_id', $this->integer());
        $this->addColumn('{{%v2_posts}}', 'uid', $this->bigInteger()->notNull());
        $this->dropColumn('{{%v2_posts}}', 'lang');
        $this->dropColumn('{{%v2_posts}}', 'id');
        $this->execute('DROP TYPE enum_lang');
        $this->alterColumn('{{%v2_posts}}', 'active', "SET DEFAULT 'f'");
    }
}
