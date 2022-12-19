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
    public const ADD_UNIQUE = MigrationRecordBuilder::INDENT . "\$this->createIndex('%s', '%s', '%s', true);";
    public const ADD_INDEX = MigrationRecordBuilder::INDENT . "\$this->createIndex('%s', '%s', '%s', %s);";
    public const DROP_COLUMN = MigrationRecordBuilder::INDENT . "\$this->dropColumn('%s', '%s');";
    public const ADD_ENUM = MigrationRecordBuilder::INDENT . "\$this->execute('CREATE TYPE enum_%s AS ENUM(%s)');";
    public const DROP_ENUM = MigrationRecordBuilder::INDENT . "\$this->execute('DROP TYPE enum_%s');";
    public const DROP_TABLE = MigrationRecordBuilder::INDENT . "\$this->dropTable('%s');";
    public const ADD_FK = MigrationRecordBuilder::INDENT . "\$this->addForeignKey('%s', '%s', '%s', '%s', '%s');";
    public const ADD_PK = MigrationRecordBuilder::INDENT . "\$this->addPrimaryKey('%s', '%s', '%s');";
    public const ADD_COLUMN = MigrationRecordBuilder::INDENT . "\$this->addColumn('%s', '%s', %s);";

    public const ADD_COLUMN_RAW = MigrationRecordBuilder::INDENT . "\$this->db->createCommand(\"ALTER TABLE %s ADD COLUMN %s %s\")->execute();";

    public const ALTER_COLUMN = MigrationRecordBuilder::INDENT . "\$this->alterColumn('%s', '%s', %s);";

    public const ALTER_COLUMN_RAW = MigrationRecordBuilder::INDENT . "\$this->db->createCommand(\"ALTER TABLE %s MODIFY %s %s\")->execute();";
    public const ALTER_COLUMN_RAW_PGSQL = MigrationRecordBuilder::INDENT . "\$this->db->createCommand(\"ALTER TABLE %s ALTER COLUMN %s SET DATA TYPE %s\")->execute();";

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
        // $codeColumns = array_map(function (ColumnSchema $column) {
        //     return $this->columnToCode($column, false)->getCode();
        // }, $columns);
        foreach ($columns as $columnName => $cebeDbColumnSchema) {
            if (is_string($cebeDbColumnSchema->xDbType) && !empty($cebeDbColumnSchema->xDbType)) {
                $codeColumns[] = $columnName.' '.$this->columnToCode($cebeDbColumnSchema, false)->getCode();
            } else {
                $codeColumns[$columnName] = $this->columnToCode($cebeDbColumnSchema, false)->getCode();
            }
        }

        // VarDumper::dump('$codeColumns');
        // VarDumper::dump($codeColumns);
        // VarDumper::dump($columns);

        $codeColumns = str_replace([PHP_EOL, "\\\'"], [PHP_EOL . self::INDENT, "'"], VarDumper::export($codeColumns));
        return sprintf(self::ADD_TABLE, $tableAlias, $codeColumns);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function addColumn(string $tableAlias, ColumnSchema $column):string
    {
        if (is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($column, false);
            return sprintf(self::ADD_COLUMN_RAW, $tableAlias, $column->name, $converter->getCode());
        }

        $converter = $this->columnToCode($column, false);
        return sprintf(self::ADD_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function addDbColumn(string $tableAlias, ColumnSchema $column):string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($column, true);
            return sprintf(self::ADD_COLUMN_RAW, $tableAlias, $column->name, $converter->getCode());
        }
        $converter = $this->columnToCode($column, true);
        return sprintf(self::ADD_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function alterColumn(string $tableAlias, ColumnSchema $column):string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($column, true, false, true, true);
            return sprintf(
                ApiGenerator::isPostgres() ? self::ALTER_COLUMN_RAW_PGSQL : self::ALTER_COLUMN_RAW,
                $tableAlias, $column->name, $converter->getCode()
            );
        }
        $converter = $this->columnToCode($column, true);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getCode(true));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function alterColumnType(string $tableAlias, ColumnSchema $column, bool $addUsing = false):string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($column, false, false, true, true);
            return sprintf(
                ApiGenerator::isPostgres() ? self::ALTER_COLUMN_RAW_PGSQL : self::ALTER_COLUMN_RAW,
                $tableAlias,
                $column->name,
                rtrim(ltrim($converter->getAlterExpression($addUsing), "'"), "'")
            );
        }
        $converter = $this->columnToCode($column, false);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getAlterExpression($addUsing));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function alterColumnTypeFromDb(string $tableAlias, ColumnSchema $column, bool $addUsing = false) :string
    {
        if (property_exists($column, 'xDbType') && is_string($column->xDbType) && !empty($column->xDbType)) {
            $converter = $this->columnToCode($column, true, false, true, true);
            return sprintf(
                ApiGenerator::isPostgres() ? self::ALTER_COLUMN_RAW_PGSQL : self::ALTER_COLUMN_RAW,
                $tableAlias,
                $column->name,
                rtrim(ltrim($converter->getAlterExpression($addUsing), "'"), "'")
            );
        }
        $converter = $this->columnToCode($column, true);
        return sprintf(self::ALTER_COLUMN, $tableAlias, $column->name, $converter->getAlterExpression($addUsing));
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function setColumnDefault(string $tableAlias, ColumnSchema $column):string
    {
        $default = $this->columnToCode($column, false, true)->getDefaultValue();
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
        $default = $this->columnToCode($column, true, true)->getDefaultValue();
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

    public function addUniqueIndex(string $tableAlias, string $indexName, array $columns):string
    {
        return sprintf(self::ADD_UNIQUE, $indexName, $tableAlias, implode(',', $columns));
    }

    public function addIndex(string $tableAlias, string $indexName, array $columns, ?string $using = null):string
    {
        $indexType = $using === null ? 'false' : "'".ColumnToCode::escapeQuotes($using)."'";
        return sprintf(self::ADD_INDEX, $indexName, $tableAlias, implode(',', $columns), $indexType);
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

    public function dropIndex(string $tableAlias, string $indexName):string
    {
        return sprintf(self::DROP_INDEX, $indexName, $tableAlias);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function columnToCode(
        ColumnSchema $column,
        bool $fromDb = false,
        bool $alter = false,
        bool $raw = false,
        bool $alterByXDbType = false
    ): ColumnToCode
    {
        return Yii::createObject(ColumnToCode::class, [
            $this->dbSchema,
            $column,
            $fromDb,
            $alter,
            $raw,
            $alterByXDbType
        ]);
    }

    // public static function bnm789() // TODO rename
    // {
    //     $cols = [
    //         'id' => '$this->primaryKey()',
    //         'name' => '$this->string(254)->notNull()->defaultValue(\"Horse-2\")',
    //         'tag' => '$this->text()->null()->defaultValue(null)',
    //         'first_name' => '$this->string()->null()->defaultValue(null)',
    //         'string_col' => '$this->text()->null()->defaultValue(null)',
    //         'dec_col' => 'decimal(12,2) NULL DEFAULT 3.14',
    //         'str_col_def' => '$this->string()->notNull()',
    //         'json_col' => '$this->text()->null()->defaultValue(null)',
    //     ];


    // }
}
