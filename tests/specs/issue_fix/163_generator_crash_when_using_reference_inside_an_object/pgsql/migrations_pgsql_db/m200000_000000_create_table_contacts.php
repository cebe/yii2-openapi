<?php

/**
 * Table for Contact
 */
class m200000_000000_create_table_contacts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%contacts}}', [
            'id' => $this->primaryKey(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%contacts}}');
    }
}
