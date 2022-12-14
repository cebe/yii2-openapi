<?php

/**
 * Table for Menu
 */
class m200000_000000_create_table_menus extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%menus}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(100)->notNull(),
            'parent_id' => $this->bigInteger()->null()->defaultValue(null),
            0 => 'args text[] NULL DEFAULT \'{"foo","bar","baz"}\'',
            1 => 'kwargs json NOT NULL DEFAULT \'[{"foo":"bar"},{"buzz":"fizz"}]\'',
        ]);
        $this->addForeignKey('fk_menus_parent_id_menus_id', '{{%menus}}', 'parent_id', '{{%menus}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_menus_parent_id_menus_id', '{{%menus}}');
        $this->dropTable('{{%menus}}');
    }
}
