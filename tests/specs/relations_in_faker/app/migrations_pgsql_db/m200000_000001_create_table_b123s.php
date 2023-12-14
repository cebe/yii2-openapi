<?php

/**
 * Table for B123
 */
class m200000_000001_create_table_b123s extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%b123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null()->defaultValue(null),
            'c123_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_b123s_c123_id_c123s_id', '{{%b123s}}', 'c123_id', '{{%c123s}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_b123s_c123_id_c123s_id', '{{%b123s}}');
        $this->dropTable('{{%b123s}}');
    }
}
