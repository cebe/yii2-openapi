<?php

/**
 * Table for Twocol
 */
class m200000_000007_change_table_twocols extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%twocols}}', 'email', $this->text()->null()->first());
        $this->addColumn('{{%twocols}}', 'last_name', $this->text()->null()->after('email'));
    }

    public function down()
    {
        $this->dropColumn('{{%twocols}}', 'last_name');
        $this->dropColumn('{{%twocols}}', 'email');
    }
}
