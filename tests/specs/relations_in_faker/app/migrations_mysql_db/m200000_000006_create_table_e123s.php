<?php

/**
 * Table for E123
 */
class m200000_000006_create_table_e123s extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%e123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null(),
            'b123_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_e123s_b123_id_b123s_id', '{{%e123s}}', 'b123_id', '{{%b123s}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_e123s_b123_id_b123s_id', '{{%e123s}}');
        $this->dropTable('{{%e123s}}');
    }
}
