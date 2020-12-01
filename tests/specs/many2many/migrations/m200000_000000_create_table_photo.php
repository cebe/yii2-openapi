<?php

/**
 * Table for Photo
 */
class m200000_000000_create_table_photo extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%photo}}', [
            'id' => $this->bigPrimaryKey(),
            'filename' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%photo}}');
    }
}
