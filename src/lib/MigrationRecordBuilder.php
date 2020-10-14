<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\VarDumper;
use function sprintf;
use function str_replace;

class MigrationRecordBuilder
{
    public const INDENT = '        ';
    public const DROP_INDEX = MigrationRecordBuilder::INDENT . "\$this->dropIndex('%s', '%s');";
    public const DROP_FK = MigrationRecordBuilder::INDENT . "\$this->dropForeignKey('%s', '%s');";
    public const ADD_TABLE = MigrationRecordBuilder::INDENT . "\$this->createTable('%s', %s);";
    public const ADD_UNIQUE = MigrationRecordBuilder::INDENT . "\$this->createIndex('%s', '%s', '%s', true);";
    public const DROP_COLUMN = MigrationRecordBuilder::INDENT . "\$this->dropColumn('%s', '%s');";
    public const ADD_ENUM = MigrationRecordBuilder::INDENT . "\$this->execute('CREATE TYPE enum_%s AS ENUM(%s)');";
    public const DROP_ENUM = MigrationRecordBuilder::INDENT . "\$this->execute('DROP TYPE enum_%s');";
    public const DROP_TABLE = MigrationRecordBuilder::INDENT . "\$this->dropTable('%s');";
    public const ADD_FK = MigrationRecordBuilder::INDENT . "\$this->addForeignKey('%s', '%s', '%s', '%s', '%s');";
    public const ADD_COLUMN = MigrationRecordBuilder::INDENT . "\$this->addColumn('%s', '%s', %s);";
    public const ALTER_COLUMN = MigrationRecordBuilder::INDENT . "\$this->alterColumn('%s', '%s', %s);";

    /**
     * @var \yii\db\Schema
     */
    private $dbSchema;

    public function __construct(Schema $dbSchema)
    {
        $this->dbSchema = $dbSchema;
    }

    /**
     * @param string                     $tableAlias
     * @param array|\yii\db\ColumnSchema $columns
     * @param array                      $uniqueNames
     * @return string
     */
    public function createTable(string $tableAlias, array $columns, array $uniqueNames):string
    {
        $codeColumns = array_map(
            function (ColumnSchema $column) use ($uniqueNames) {
                $isUnique = in_array($column->name, $uniqueNames, true);
                $converter = $this->columnToCode($column, $isUnique, false);
                return $converter->getCode();
            },
            $columns
        );
        $codeColumns = str_replace([PHP_EOL, "\\\'"], [PHP_EOL . self::INDENT, "'"], VarDumper::export($codeColumns));
        return sprintf(self::ADD_TABLE, $tableAlias, $codeColumns);
    }

    public function addColumn(string $tableAlias, ColumnSchema $column, bool $isUnique = false):string
    {
        $converter = $this->columnToCode($column, $isUnique, false);
        return sprintf(self::ADD_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    public function addDbColumn(string $tableAlias, ColumnSchema $column, bool $isUnique = false):string
    {
        $converter = $this->columnToCode($column, $isUnique, true);
        return sprintf(self::ADD_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    public function alterColumn(string $tableAlias, ColumnSchema $column):string
    {
        $converter = $this->columnToCode($column, false, true);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }


    public function alterColumnType(string $tableAlias, ColumnSchema $column):string
    {
        $converter = $this->columnToCode($column, false, false);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getTypeAndNullState());
    }

    public function alterColumnTypeFromDb(string $tableAlias, ColumnSchema $column):string
    {
        $converter = $this->columnToCode($column, false, true);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getTypeAndNullState());
    }

    public function setColumnDefault(string $tableAlias, ColumnSchema $column):string
    {
        $converter = $this->columnToCode($column, false, false);
        $default = $converter->getDefaultValue();
        if ($default === null) {
            return '';
        }
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, '"SET DEFAULT '.$default.'"');
    }

    public function setColumnDefaultFromDb(string $tableAlias, ColumnSchema $column):string
    {
        $converter = $this->columnToCode($column, false, true);
        $default = $converter->getDefaultValue();
        if ($default === null) {
            return '';
        }
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, '"SET DEFAULT '.$default.'"');
    }

    public function dropColumnDefault(string $tableAlias, ColumnSchema $column):string
    {
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, '"DROP DEFAULT"');
    }

    public function setColumnNotNull(string $tableAlias, ColumnSchema $column):string
    {
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, '"SET NOT NULL"');
    }

    public function dropColumnNotNull(string $tableAlias, ColumnSchema $column):string
    {
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, '"DROP NOT NULL"');
    }

    public function createEnum(string $columnName, array $values):string
    {
        return sprintf(self::ADD_ENUM, $columnName, ColumnToCode::enumToString($values));
    }

    public function addFk(string $fkName, string $tableAlias, string $fkCol, string $refTable, string $refCol):string
    {
        return sprintf(self::ADD_FK, $fkName, $tableAlias, $fkCol, $refTable, $refCol);
    }

    public function addUniqueIndex(string $tableAlias, string $columnName):string
    {
        return sprintf(self::ADD_UNIQUE, 'unique_' . $columnName, $tableAlias, $columnName);
    }

    public function dropTable(string $tableAlias):string
    {
        return sprintf(self::DROP_TABLE, $tableAlias);
    }

    public function dropEnum(string $columnName):string
    {
        return sprintf(self::DROP_ENUM, $columnName);
    }

    public function dropFk(string $fkName, string $tableAlias):string
    {
        return sprintf(self::DROP_FK, $fkName, $tableAlias);
    }

    public function dropColumn(string $tableAlias, string $columnName):string
    {
        return sprintf(self::DROP_COLUMN, $tableAlias, $columnName);
    }

    public function dropUniqueIndex(string $tableAlias, string $columnName):string
    {
        return sprintf(self::DROP_INDEX, 'unique_' . $columnName, $tableAlias);
    }

    private function columnToCode(ColumnSchema $column, bool $isUnique, bool $fromDb = false): ColumnToCode
    {
        return new ColumnToCode($this->dbSchema, $column, $isUnique, $fromDb);
    }
}
