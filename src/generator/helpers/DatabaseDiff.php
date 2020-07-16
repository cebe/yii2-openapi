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
     * @param array $relations
     * @return array
     */
    public function diffTable($tableName, $columns, $relations)
    {
        $tableSchema = $this->db->getTableSchema($tableName, true);
        if ($tableSchema === null) {
            // create table
            $codeColumns = VarDumper::export(array_map(function ($c) {
                return $this->columnToDbType($c);
            }, $columns));
            $upCode = str_replace("\n", "\n        ", "        \$this->createTable('$tableName', $codeColumns);");
            $downCode = "        \$this->dropTable('$tableName');";

            $dependencies = [];
            foreach($relations as $relation) {
                if ($relation['method'] !== 'hasOne') {
                    continue;
                }
                $fkCol = reset($relation['link']);
                $fkRefCol = key($relation['link']);
                $fkRefTable = $relation['tableName'];
                $fkName = $this->foreignKeyName($tableName, $fkCol, $fkRefTable, $fkRefCol);
                $upCode .= "\n        \$this->addForeignKey('$fkName', '$tableName', '$fkCol', '$fkRefTable', '$fkRefCol');";
                $downCode = "        \$this->dropForeignKey('$fkName', '$tableName');\n$downCode";
                $dependencies[] = $fkRefTable;
            }

            return [$upCode, $downCode, $dependencies, 'create_table_' . $this->normalizeTableName($tableName)];
        }

        $upCode = [];
        $downCode = [];

        // compare existing columns with expected columns
        $wantNames = array_keys($columns);
        $haveNames = $tableSchema->columnNames;
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
            $oldDbType = $this->columnToDbType($tableSchema->columns[$unknownColumn]);
            $downCode[] = "\$this->addColumn('$tableName', '$unknownColumn', '$oldDbType');";
        }

        // compare desired type with existing type
        foreach ($tableSchema->columns as $columnName => $currentColumnSchema) {
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
                    $upCode[] = "\$this->alterColumn('$tableName', '$columnName', '{$this->escapeQuote($this->columnToDbType($desiredColumnSchema))}');";
                    $downCode[] = "\$this->alterColumn('$tableName', '$columnName', '{$this->escapeQuote($this->columnToDbType($currentColumnSchema))}');";
            }
        }

        // compare existing foreign keys with relations
        $dependencies = [];
        foreach($relations as $relation) {
            if ($relation['method'] !== 'hasOne') {
                continue;
            }
            $fkCol = reset($relation['link']);
            $fkRefCol = key($relation['link']);
            $fkRefTable = $relation['tableName'];
            $fkName = $this->foreignKeyName($tableName, $fkCol, $fkRefTable, $fkRefCol);

            if (isset($tableSchema->foreignKeys[$fkName])) {
                continue;
            }
            $upCode[] = "\$this->addForeignKey('$fkName', '$tableName', '$fkCol', '$fkRefTable', '$fkRefCol');";
            array_unshift($downCode, "\$this->dropForeignKey('$fkName', '$tableName');");
            $dependencies[] = $fkRefTable;
        }

        if (empty($upCode) && empty($downCode)) {
            return ['', '', [], ''];
        }

        return [
            "        " . implode("\n        ", $upCode),
            "        " . implode("\n        ", $downCode),
            [],
            'change_table_' . $this->normalizeTableName($tableName),
        ];
    }

    private function foreignKeyName($table, $column, $foreignTable, $foreignColumn)
    {
        $table = $this->normalizeTableName($table);
        $foreignTable = $this->normalizeTableName($foreignTable);
        return "fk_{$table}_{$column}_{$foreignTable}_{$foreignColumn}";
    }

    private function normalizeTableName($tableName)
    {
        if (preg_match('~^{{%?(.*)}}$~', $tableName, $m)) {
            return $m[1];
        }
        return $tableName;
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
