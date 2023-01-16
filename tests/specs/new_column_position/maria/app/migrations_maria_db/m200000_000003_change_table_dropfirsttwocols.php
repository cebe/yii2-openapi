<?php

/**
 * Table for Dropfirsttwocol
 */
class m200000_000003_change_table_dropfirsttwocols extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%dropfirsttwocols}}', 'name');
        $this->dropColumn('{{%dropfirsttwocols}}', 'address');
    }

    public function down()
    {
        $this->addColumn('{{%dropfirsttwocols}}', 'address', $this->text()->null()->defaultValue(null));
        $this->addColumn('{{%dropfirsttwocols}}', 'name', $this->text()->null()->defaultValue(null)->first());
    }
}
