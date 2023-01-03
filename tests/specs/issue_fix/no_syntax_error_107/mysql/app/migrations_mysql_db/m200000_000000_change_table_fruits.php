<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->db->createCommand('ALTER TABLE {{%fruits}} ADD COLUMN test_emails json NOT NULL')->execute();
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->notNull());
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->string(255)->null()->defaultValue(null));
        $this->dropColumn('{{%fruits}}', 'test_emails');
    }
}
