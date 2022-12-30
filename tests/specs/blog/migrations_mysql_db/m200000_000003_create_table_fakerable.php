<?php

/**
 * Table for Fakerable
 */
class m200000_000003_create_table_fakerable extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%fakerable}}', [
            'id' => $this->bigPrimaryKey(),
            'active' => $this->boolean()->null()->defaultValue(null),
            'floatval' => $this->float()->null()->defaultValue(null),
            'floatval_lim' => $this->float()->null()->defaultValue(null),
            'doubleval' => $this->double()->null()->defaultValue(null),
            'int_min' => $this->integer()->null()->defaultValue(3),
            'int_max' => $this->integer()->null()->defaultValue(null),
            'int_minmax' => $this->integer()->null()->defaultValue(null),
            'int_created_at' => $this->integer()->null()->defaultValue(null),
            'int_simple' => $this->integer()->null()->defaultValue(null),
            'str_text' => $this->text()->null(),
            'str_varchar' => $this->string(100)->null()->defaultValue(null),
            'str_date' => $this->date()->null()->defaultValue(null),
            'str_datetime' => $this->timestamp()->null()->defaultValue(null),
            'str_country' => $this->text()->null(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%fakerable}}');
    }
}
