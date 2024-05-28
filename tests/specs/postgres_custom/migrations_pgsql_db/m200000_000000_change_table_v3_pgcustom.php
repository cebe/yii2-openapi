<?php

/**
 * Table for Custom
 */
class m200000_000000_change_table_v3_pgcustom extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "SET DEFAULT '[]'");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "SET DEFAULT '[]'");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', "SET DEFAULT '[{\"foo\":\"foobar\"},{\"xxx\":\"yyy\"}]'");
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', "SET NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', "SET DEFAULT '{\"foo\":\"bar\",\"bar\":\"baz\"}'");
        $this->alterColumn('{{%v3_pgcustom}}', 'status', "SET DEFAULT 'draft'");
        $this->alterColumn('{{%v3_pgcustom}}', 'status_x', "SET DEFAULT 'draft'");
        $this->createIndex('v3_pgcustom_search_gin_index', '{{%v3_pgcustom}}', 'search', 'gin(to_tsvector(\'english\', status))');
    }

    public function safeDown()
    {
        $this->dropIndex('v3_pgcustom_search_gin_index', '{{%v3_pgcustom}}');
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json1', "DROP DEFAULT");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json2', "DROP DEFAULT");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json3', "SET DEFAULT '{\"bar\":\"baz\",\"foo\":\"bar\"}'");
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', "DROP NOT NULL");
        $this->alterColumn('{{%v3_pgcustom}}', 'json4', "SET DEFAULT '{\"ffo\":\"bar\"}'");
        $this->alterColumn('{{%v3_pgcustom}}', 'status', "DROP DEFAULT");
        $this->alterColumn('{{%v3_pgcustom}}', 'status_x', "DROP DEFAULT");
    }
}
