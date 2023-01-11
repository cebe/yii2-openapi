<?php

/**
 * Table for Editcolumn
 */
class m200000_000001_change_table_editcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN "first_name" varchar NULL DEFAULT NULL')->execute();
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN "json_col_def_n" json NOT NULL DEFAULT \'[]\'')->execute();
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN "json_col_def_n_2" json NOT NULL DEFAULT \'[]\'')->execute();
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ADD COLUMN "text_col_array" text[] NULL DEFAULT NULL')->execute();
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ALTER COLUMN "name" SET DATA TYPE varchar(254)')->execute();
        $this->alterColumn('{{%editcolumns}}', 'name', "SET DEFAULT 'Horse-2'");
        $this->alterColumn('{{%editcolumns}}', 'string_col', 'text NULL USING "string_col"::text');
        $this->alterColumn('{{%editcolumns}}', 'string_col', "DROP NOT NULL");
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ALTER COLUMN "dec_col" SET DATA TYPE decimal(12,2) USING "dec_col"::decimal(12,2)')->execute();
        $this->alterColumn('{{%editcolumns}}', 'dec_col', "SET DEFAULT 3.14");
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', "SET NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', "DROP DEFAULT");
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ALTER COLUMN "json_col" SET DATA TYPE text USING "json_col"::text')->execute();
        $this->alterColumn('{{%editcolumns}}', 'json_col', "SET NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'json_col', "SET DEFAULT 'fox jumps over dog'");
        $this->alterColumn('{{%editcolumns}}', 'json_col_2', "SET NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'json_col_2', "SET DEFAULT '[]'");
        $this->db->createCommand('ALTER TABLE {{%editcolumns}} ALTER COLUMN "numeric_col" SET DATA TYPE double precision USING "numeric_col"::double precision')->execute();
    }

    public function safeDown()
    {
        $this->alterColumn('{{%editcolumns}}', 'numeric_col', 'int4 NULL USING "numeric_col"::int4');
        $this->alterColumn('{{%editcolumns}}', 'json_col', 'jsonb NULL USING "json_col"::jsonb');
        $this->alterColumn('{{%editcolumns}}', 'dec_col', 'numeric NULL USING "dec_col"::numeric');
        $this->alterColumn('{{%editcolumns}}', 'string_col', 'varchar(255) NOT NULL USING "string_col"::varchar');
        $this->alterColumn('{{%editcolumns}}', 'name', $this->string(255)->notNull());
        $this->dropColumn('{{%editcolumns}}', 'text_col_array');
        $this->dropColumn('{{%editcolumns}}', 'json_col_def_n_2');
        $this->dropColumn('{{%editcolumns}}', 'json_col_def_n');
        $this->dropColumn('{{%editcolumns}}', 'first_name');
        $this->alterColumn('{{%editcolumns}}', 'name', "SET DEFAULT 'Horse'");
        $this->alterColumn('{{%editcolumns}}', 'string_col', "SET NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'dec_col', "DROP DEFAULT");
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', "DROP NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'str_col_def', "SET DEFAULT 'hi there'");
        $this->alterColumn('{{%editcolumns}}', 'json_col', "DROP NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'json_col', "DROP DEFAULT");
        $this->alterColumn('{{%editcolumns}}', 'json_col_2', "DROP NOT NULL");
        $this->alterColumn('{{%editcolumns}}', 'json_col_2', "DROP DEFAULT");
    }
}
