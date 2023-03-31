<?php

/**
 * Table for A123
 */
class m200000_000002_create_table_a123s extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%a123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null()->defaultValue(null),
            'b123_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_a123s_b123_id_b123s_id', '{{%a123s}}', 'b123_id', '{{%b123s}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_a123s_b123_id_b123s_id', '{{%a123s}}');
        $this->dropTable('{{%a123s}}');
    }
}
