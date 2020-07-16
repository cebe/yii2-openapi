<?php

namespace cebe\yii2openapi\generator\helpers;

use yii\base\Component;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * This class aims to generate database migrations from difference between OpenAPI specification
 * schema and the current database.
 *
 */
class DatabaseDiff extends Component
{
    /**
     * @var string|array|Connection the Yii database connection component for connecting to the database.
     */
    public $db = 'db';


    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * Calculate the difference between a database table and the desired data schema.
     * @param string $tableName name of the database table.
     * @param ColumnSchema[] $columns
     * @return array
     */
    public function diffTable($tableName, $columns)
    {
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema === null) {
            // create table
            $codeColumns = VarDumper::export(array_map(function ($c) {
                return $this->columnToDbType($c);
            }, $columns));
            $upCode = str_replace("\n", "\n        ", "        \$this->createTable('$tableName', $codeColumns);");
            $downCode = "        \$this->dropTable('$tableName');";
            return [$upCode, $downCode];
        }

        $upCode = [];
        $downCode = [];

        // compare existing columns with expected columns
        $wantNames = array_keys($columns);
        $haveNames = $schema->columnNames;
        sort($wantNames);
        sort($haveNames);
        $missingDiff = array_diff($wantNames, $haveNames);
        $unknownDiff = array_diff($haveNames, $wantNames);
        foreach ($missingDiff as $missingColumn) {
            $upCode[] = "\$this->addColumn('$tableName', '$missingColumn', '{$this->escapeQuote($this->columnToDbType($columns[$missingColumn]))}');";
            $downCode[] = "\$this->dropColumn('$tableName', '$missingColumn');";
        }
        foreach ($unknownDiff as $unknownColumn) {
            $upCode[] = "\$this->dropColumn('$tableName', '$unknownColumn');";
            $oldDbType = $this->columnToDbType($schema->columns[$unknownColumn]);
            $downCode[] = "\$this->addColumn('$tableName', '$unknownColumn', '$oldDbType');";
        }

        // compare desired type with existing type
        foreach ($schema->columns as $columnName => $currentColumnSchema) {
            if (!isset($columns[$columnName])) {
                continue;
            }
            $desiredColumnSchema = $columns[$columnName];
            switch (true) {
                case $desiredColumnSchema->dbType === 'pk':
                case $desiredColumnSchema->dbType === 'bigpk':
                    // do not adjust existing primary keys
                    break;
                case $desiredColumnSchema->type !== $currentColumnSchema->type:
                case $desiredColumnSchema->allowNull != $currentColumnSchema->allowNull:
                case $desiredColumnSchema->type === 'string' && $desiredColumnSchema->size != $currentColumnSchema->size:
                    print_r($currentColumnSchema);
                    print_r($desiredColumnSchema);
                    $upCode[] = "\$this->alterColumn('$tableName', '$columnName', '{$this->escapeQuote($this->columnToDbType($desiredColumnSchema))}');";
                    $downCode[] = "\$this->alterColumn('$tableName', '$columnName', '{$this->escapeQuote($this->columnToDbType($currentColumnSchema))}');";
            }
        }


        if (empty($upCode) && empty($downCode)) {
            return ['', ''];
        }

        return [
            "        " . implode("\n        ", $upCode),
            "        " . implode("\n        ", $downCode),
        ];
    }

    private function escapeQuote($str)
    {
        return str_replace("'", "\\'", $str);
    }

    private function columnToDbType(ColumnSchema $column)
    {
        if ($column->dbType === 'pk') {
            return $column->dbType;
        }
        return $column->dbType . ($column->size ? "({$column->size})" : '') . ($column->allowNull ? '' : ' NOT NULL');
    }
}
