<?php

/**
 * Table for Custom
 */
class m200000_000000_create_table_v3_pgcustom extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%v3_pgcustom}}', [
            'id' => $this->bigPrimaryKey(),
            'num' => $this->integer()->null()->defaultValue(0),
            'json1' => 'json NOT NULL DEFAULT \'[]\'',
            'json2' => 'json NOT NULL DEFAULT \'[]\'',
            'json3' => 'json NOT NULL DEFAULT \'[{"foo":"foobar"},{"xxx":"yyy"}]\'',
            'json4' => 'json NOT NULL DEFAULT \'{"foo":"bar","bar":"baz"}\'',
            'status' => 'enum(\'draft\', \'pending\', \'active\') NULL DEFAULT \'draft\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%v3_pgcustom}}');
    }
}
