<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\migrations;

use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\ColumnToCode;
use Yii;
use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\VarDumper;
use function implode;
use function sprintf;
use function str_replace;

final class MigrationRecordBuilder
{
    public const INDENT = '        ';
    public const DROP_INDEX = MigrationRecordBuilder::INDENT . "\$this->dropIndex('%s', '%s');";
    public const DROP_FK = MigrationRecordBuilder::INDENT . "\$this->dropForeignKey('%s', '%s');";
    public const DROP_PK = MigrationRecordBuilder::INDENT . "\$this->dropPrimaryKey('%s', '%s');";
    public const ADD_TABLE = MigrationRecordBuilder::INDENT . "\$this->createTable('%s', %s);";
    public const ADD_UNIQUE = MigrationRecordBuilder::INDENT . "\$this->createIndex('%s', '%s', %s, true);";
    public const ADD_INDEX = MigrationRecordBuilder::INDENT . "\$this->createIndex('%s', '%s', %s, %s);";
    public const DROP_COLUMN = MigrationRecordBuilder::INDENT . "\$this->dropColumn('%s', '%s');";
    public const ADD_ENUM = MigrationRecordBuilder::INDENT . "\$this->execute('CREATE TYPE \"enum_%s_%s\" AS ENUM(%s)');";
    public const DROP_ENUM = MigrationRecordBuilder::INDENT . "\$this->execute('DROP TYPE \"enum_%s_%s\"');";
    public const DROP_TABLE = MigrationRecordBuilder::INDENT . "\$this->dropTable('%s');";

    public const ADD_FK = MigrationRecordBuilder::INDENT . "\$this->addForeignKey('%s', '%s', '%s', '%s', '%s');";
    public const ADD_FK_WITH_JUST_ON_DELETE = MigrationRecordBuilder::INDENT . "\$this->addForeignKey('%s', '%s', '%s', '%s', '%s', '%s');";
    public const ADD_FK_WITH_ON_UPDATE = MigrationRecordBuilder::INDENT . "\$this->addForeignKey('%s', '%s', '%s', '%s', '%s', %s, '%s');";

    public const ADD_PK = MigrationRecordBuilder::INDENT . "\$this->addPrimaryKey('%s', '%s', '%s');";
    public const ADD_COLUMN = MigrationRecordBuilder::INDENT . "\$this->addColumn('%s', '%s', %s);";
    public const ALTER_COLUMN = MigrationRecordBuilder::INDENT . "\$this->alterColumn('%s', '%s', %s);";


    public const ADD_COLUMN_RAW = MigrationRecordBuilder::INDENT . "\$this->db->createCommand('ALTER TABLE %s ADD COLUMN %s %s')->execute();";

    public const ALTER_COLUMN_RAW = MigrationRecordBuilder::INDENT . "\$this->db->createCommand('ALTER TABLE %s MODIFY %s %s')->execute();";

    public const ALTER_COLUMN_RAW_PGSQL = MigrationRecordBuilder::INDENT . "\$this->db->createCommand('ALTER TABLE %s ALTER COLUMN \"%s\" SET DATA TYPE %s')->execute();";

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
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function createTable(string $tableAlias, array $columns):string
    {
        $codeColumns = [];
        foreach ($columns as $columnName => $cebeDbColumnSchema) {
            if (is_string($cebeDbColumnSchema->xDbType) && !empty($cebeDbColumnSchema->xDbType)) {
                $name = static::quote($columnName);
                $codeColumns[] = $name.' '.$this->columnToCode($tableAlias, $cebeDbColumnSchema, false)->getCode();
            } else {
                $codeColumns[$columnName] = $this->columnToCode($tableAlias, $cebeDbColumnSchema, false)->getCode();
            }
        }

        $codeColumns = static::makeString($codeColumns);

        return sprintf(self::ADD_TABLE, $tableAlias, $codeColumns);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function addColumn(string $tableAlias, ColumnSchema $column, ?string $position = null):string
    {
        // $converter = $this->columnToCode($column, false, false, $position);
        if (is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($tableAlias, $column, false, false, false, false, $position);
            $name = static::quote($column->name);
            return sprintf(self::ADD_COLUMN_RAW, $tableAlias, $name, ColumnToCode::escapeQuotes($converter->getCode()));
        }

        $converter = $this->columnToCode($tableAlias, $column, false, false, false, false, $position);
        return sprintf(self::ADD_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function addDbColumn(string $tableAlias, ColumnSchema $column, ?string $position = null):string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($tableAlias, $column, true, false, false, false, $position);
            $name = static::quote($column->name);
            return sprintf(self::ADD_COLUMN_RAW, $tableAlias, $column->name, ColumnToCode::escapeQuotes($converter->getCode()));
        }
        $converter = $this->columnToCode($tableAlias, $column, true, false, false, false, $position);
        return sprintf(self::ADD_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function alterColumn(string $tableAlias, ColumnSchema $column):string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($tableAlias, $column, true, false, true, true);
            return sprintf(
                ApiGenerator::isPostgres() ? self::ALTER_COLUMN_RAW_PGSQL : self::ALTER_COLUMN_RAW,
                $tableAlias,
                $column->name,
                ColumnToCode::escapeQuotes($converter->getCode())
            );
        }
        $converter = $this->columnToCode($tableAlias, $column, true);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function alterColumnType(string $tableAlias, ColumnSchema $column, bool $addUsing = false):string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($tableAlias, $column, false, false, true, true);
            return sprintf(
                ApiGenerator::isPostgres() ? self::ALTER_COLUMN_RAW_PGSQL : self::ALTER_COLUMN_RAW,
                $tableAlias,
                $column->name,
                rtrim(ltrim($converter->getAlterExpression($addUsing), "'"), "'")
            );
        }
        $converter = $this->columnToCode($tableAlias, $column, false);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getAlterExpression($addUsing));
    }

    /**
     * This method is only used in Pgsql
     * @throws \yii\base\InvalidConfigException
     */
    public function alterColumnTypeFromDb(string $tableAlias, ColumnSchema $column, bool $addUsing = false) :string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($tableAlias, $column, true, false, true, true);
            return sprintf(
                ApiGenerator::isPostgres() ? self::ALTER_COLUMN_RAW_PGSQL : self::ALTER_COLUMN_RAW,
                $tableAlias,
                $column->name,
                rtrim(ltrim($converter->getAlterExpression($addUsing), "'"), "'")
            );
        }
        $converter = $this->columnToCode($tableAlias, $column, true);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getAlterExpression($addUsing));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function setColumnDefault(string $tableAlias, ColumnSchema $column):string
    {
        $default = $this->columnToCode($tableAlias, $column, false, true)->getDefaultValue();
        if ($default === null) {
            return '';
        }
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, '"SET DEFAULT '.$default.'"');
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function setColumnDefaultFromDb(string $tableAlias, ColumnSchema $column):string
    {
        $default = $this->columnToCode($tableAlias, $column, true, true)->getDefaultValue();
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

    public function createEnum(string $tableAlias, string $columnName, array $values):string
    {
        $rawTableName = $this->dbSchema->getRawTableName($tableAlias);
        return sprintf(self::ADD_ENUM, $rawTableName, $columnName, ColumnToCode::enumToString($values));
    }

    public function addFk(string $fkName, string $tableAlias, string $fkCol, string $refTable, string $refCol, ?string $onDelete = null, ?string $onUpdate = null):string
    {
        if ($onUpdate === null && $onDelete === null) {
            return sprintf(self::ADD_FK, $fkName, $tableAlias, $fkCol, $refTable, $refCol);
        } elseif ($onDelete !== null && $onUpdate === null) {
            return sprintf(self::ADD_FK_WITH_JUST_ON_DELETE, $fkName, $tableAlias, $fkCol, $refTable, $refCol, $onDelete);
        } elseif ($onUpdate !== null) {
            return sprintf(
                self::ADD_FK_WITH_ON_UPDATE,
                $fkName,
                $tableAlias,
                $fkCol,
                $refTable,
                $refCol,
                $onDelete === null ? 'null' : "'$onDelete'",
                $onUpdate
            );
        }
    }

    public function addUniqueIndex(string $tableAlias, string $indexName, array $columns):string
    {
        return sprintf(
            self::ADD_UNIQUE,
            $indexName,
            $tableAlias,
            count($columns) === 1 ? "'{$columns[0]}'" : '["'.implode('", "', $columns).'"]'
        );
    }

    public function addIndex(string $tableAlias, string $indexName, array $columns, ?string $using = null):string
    {
        $indexType = $using === null ? 'false' : "'".ColumnToCode::escapeQuotes($using)."'";
        return sprintf(
            self::ADD_INDEX,
            $indexName,
            $tableAlias,
            count($columns) === 1 ? "'{$columns[0]}'" : '["'.implode('", "', $columns).'"]',
            $indexType
        );
    }

    public function addPrimaryKey(string $tableAlias, array $columns, string $pkName= null):string
    {
        $pkName = $pkName ?? ('pk_'. implode('_', $columns));
        return sprintf(self::ADD_PK, $pkName, $tableAlias, implode(',', $columns));
    }

    public function dropPrimaryKey(string $tableAlias, array $columns, string $pkName = null):string
    {
        $pkName = $pkName ?? ('pk_'. implode('_', $columns));
        return sprintf(self::DROP_PK, $pkName, $tableAlias);
    }

    public function dropTable(string $tableAlias):string
    {
        return sprintf(self::DROP_TABLE, $tableAlias);
    }

    public function dropEnum(string $tableAlias, string $columnName):string
    {
        $rawTableName = $this->dbSchema->getRawTableName($tableAlias);
        return sprintf(self::DROP_ENUM, $rawTableName, $columnName);
    }

    public function dropFk(string $fkName, string $tableAlias):string
    {
        return sprintf(self::DROP_FK, $fkName, $tableAlias);
    }

    public function dropColumn(string $tableAlias, string $columnName):string
    {
        return sprintf(self::DROP_COLUMN, $tableAlias, $columnName);
    }

    public function dropIndex(string $tableAlias, string $indexName):string
    {
        return sprintf(self::DROP_INDEX, $indexName, $tableAlias);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function columnToCode(
        string $tableAlias,
        ColumnSchema $column,
        bool $fromDb = false,
        bool $alter = false,
        bool $raw = false,
        bool $alterByXDbType = false,
        ?string $position = null
    ): ColumnToCode {
        return Yii::createObject(ColumnToCode::class, [
            $this->dbSchema,
            $tableAlias,
            $column,
            $fromDb,
            $alter,
            $raw,
            $alterByXDbType,
            $position
        ]);
    }

    // https://github.com/cebe/yii2-openapi/issues/127
    public static function quote(string $columnName): string
    {
        if (ApiGenerator::isPostgres()) {
            return '"'.$columnName.'"';
        }
        return $columnName;
    }

    /**
     * Convert code columns array to comlpete syntactically correct PHP code string which will be written to migration file
     */
    public static function makeString(array $codeColumns): string
    {
        $finalStr = ''.PHP_EOL;
        foreach ($codeColumns as $key => $column) {
            if (is_string($key)) {
                if (substr($column, 0, 5) === '$this') {
                    $finalStr .= VarDumper::export($key).' => '.$column.','.PHP_EOL;
                } else {
                    $finalStr .= VarDumper::export($key).' => '.VarDumper::export($column).','.PHP_EOL;
                }
            } else {
                $finalStr .= VarDumper::export($key).' => '.VarDumper::export($column).','.PHP_EOL;
            }
        }

        $codeColumns = str_replace([PHP_EOL], [PHP_EOL . self::INDENT.'    '], $finalStr);
        $codeColumns = trim($codeColumns);
        $codeColumns = '['.PHP_EOL.self::INDENT.'    '.$codeColumns.PHP_EOL . self::INDENT.']';
        return $codeColumns;
    }
}
