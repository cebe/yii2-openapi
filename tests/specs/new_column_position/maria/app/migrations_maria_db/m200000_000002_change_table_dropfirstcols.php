<?php

/**
 * Table for Dropfirstcol
 */
class m200000_000002_change_table_dropfirstcols extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%dropfirstcols}}', 'name');
    }

    public function down()
    {
        $this->addColumn('{{%dropfirstcols}}', 'name', $this->text()->null()->defaultValue(null)->first());
    }
}
