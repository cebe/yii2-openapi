<?php

/**
 * Table for Account
 */
class m200000_000003_create_table_accounts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%accounts}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(40)->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%accounts}}');
    }
}
