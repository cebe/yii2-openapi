<?php

/**
 * Table for Webhook
 */
class m200000_000002_create_table_webhooks extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%webhooks}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null(),
            'user_id' => $this->integer()->null()->defaultValue(null),
            'redelivery_of' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_webhooks_user_id_users_id', '{{%webhooks}}', 'user_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_webhooks_redelivery_of_deliveries_id', '{{%webhooks}}', 'redelivery_of', '{{%deliveries}}', 'id');
        // TOOD add index names
        $this->createIndex('...', '{{%webhooks}}', ['user_id', 'name'], true);
        $this->createIndex('...', '{{%webhooks}}', ['redelivery_of', 'name'], true);
    }

    public function down()
    {
        $this->dropForeignKey('fk_webhooks_redelivery_of_deliveries_id', '{{%webhooks}}');
        $this->dropForeignKey('fk_webhooks_user_id_users_id', '{{%webhooks}}');
        $this->dropTable('{{%webhooks}}');
    }
}
