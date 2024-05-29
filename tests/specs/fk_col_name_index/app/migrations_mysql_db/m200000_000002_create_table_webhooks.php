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
            'name' => $this->string(255)->null()->defaultValue(null),
            'user_id' => $this->integer()->null()->defaultValue(null),
            'redelivery_of' => $this->integer()->null()->defaultValue(null),
            'rd_abc_2' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->createIndex('webhooks_user_id_name_key', '{{%webhooks}}', ["user_id", "name"], true);
        $this->createIndex('webhooks_redelivery_of_name_key', '{{%webhooks}}', ["redelivery_of", "name"], true);
        $this->createIndex('webhooks_rd_abc_2_name_key', '{{%webhooks}}', ["rd_abc_2", "name"], true);
        $this->addForeignKey('fk_webhooks_user_id_users_id', '{{%webhooks}}', 'user_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_webhooks_redelivery_of_deliveries_id', '{{%webhooks}}', 'redelivery_of', '{{%deliveries}}', 'id');
        $this->addForeignKey('fk_webhooks_rd_abc_2_deliveries_id', '{{%webhooks}}', 'rd_abc_2', '{{%deliveries}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_webhooks_rd_abc_2_deliveries_id', '{{%webhooks}}');
        $this->dropForeignKey('fk_webhooks_redelivery_of_deliveries_id', '{{%webhooks}}');
        $this->dropForeignKey('fk_webhooks_user_id_users_id', '{{%webhooks}}');
        $this->dropIndex('webhooks_rd_abc_2_name_key', '{{%webhooks}}');
        $this->dropIndex('webhooks_redelivery_of_name_key', '{{%webhooks}}');
        $this->dropIndex('webhooks_user_id_name_key', '{{%webhooks}}');
        $this->dropTable('{{%webhooks}}');
    }
}
