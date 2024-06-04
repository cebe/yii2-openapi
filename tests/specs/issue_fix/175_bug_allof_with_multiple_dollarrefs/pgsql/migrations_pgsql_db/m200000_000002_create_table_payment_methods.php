<?php

/**
 * Table for PaymentMethod
 */
class m200000_000002_create_table_payment_methods extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%payment_methods}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->notNull(),
        ]);
        $this->createIndex('payment_methods_name_key', '{{%payment_methods}}', 'name', true);
    }

    public function down()
    {
        $this->dropIndex('payment_methods_name_key', '{{%payment_methods}}');
        $this->dropTable('{{%payment_methods}}');
    }
}
