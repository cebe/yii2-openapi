<?php

/**
 * Table for Account
 */
class m200000_000000_create_table_accounts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%accounts}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull(),
            'paymentMethodName' => $this->text()->null(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%accounts}}');
    }
}
