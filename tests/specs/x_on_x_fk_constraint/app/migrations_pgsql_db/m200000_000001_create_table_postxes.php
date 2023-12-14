<?php

/**
 * Table for Postx
 */
class m200000_000001_create_table_postxes extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%postxes}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->null()->defaultValue(null),
            'user_id' => $this->integer()->null()->defaultValue(null),
            'user_2_id' => $this->integer()->null()->defaultValue(null),
            'user_3_id' => $this->integer()->null()->defaultValue(null),
            'user_4_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_postxes_user_id_userxes_id', '{{%postxes}}', 'user_id', '{{%userxes}}', 'id', null, 'CASCADE');
        $this->addForeignKey('fk_postxes_user_2_id_userxes_id', '{{%postxes}}', 'user_2_id', '{{%userxes}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_postxes_user_3_id_userxes_id', '{{%postxes}}', 'user_3_id', '{{%userxes}}', 'id', 'SET NULL');
        $this->addForeignKey('fk_postxes_user_4_id_userxes_id', '{{%postxes}}', 'user_4_id', '{{%userxes}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_postxes_user_4_id_userxes_id', '{{%postxes}}');
        $this->dropForeignKey('fk_postxes_user_3_id_userxes_id', '{{%postxes}}');
        $this->dropForeignKey('fk_postxes_user_2_id_userxes_id', '{{%postxes}}');
        $this->dropForeignKey('fk_postxes_user_id_userxes_id', '{{%postxes}}');
        $this->dropTable('{{%postxes}}');
    }
}
