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
            'search' => 'tsvector NULL',
        ]);
        $this->createIndex('v3_pgcustom_search_gin_index', '{{%v3_pgcustom}}', 'search', 'gin(to_tsvector(\'english\'))');
    }

    public function down()
    {
        $this->dropIndex('v3_pgcustom_search_gin_index', '{{%v3_pgcustom}}');
        $this->dropTable('{{%v3_pgcustom}}');
    }
}
