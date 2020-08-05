<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\MigrationModel;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\helpers\VarDumper;
use function array_intersect;
use function array_map;
use function in_array;
use function sprintf;

class MigrationBuilder
{
    public const INDENT = '        ';
    private const ADD_TABLE = self::INDENT . "\$this->createTable('%s', %s);";
    private const DROP_TABLE = self::INDENT . "\$this->dropTable('%s');";
    private const ADD_COLUMN = self::INDENT . "\$this->addColumn('%s', '%s', %s);";
    private const DROP_COLUMN = self::INDENT . "\$this->dropColumn('%s', '%s');";
    private const ALTER_COLUMN = self::INDENT . "\$this->alterColumn('%s', '%s', %s);";
    private const ADD_FK = self::INDENT . "\$this->addForeignKey('%s', '%s', '%s', '%s', '%s');";
    private const DROP_FK = self::INDENT . "\$this->dropForeignKey('%s', '%s');";
    private const ADD_PK = self::INDENT . "\$this->addPrimaryKey('%s', '%s', '%s');";
    private const ADD_ENUM = self::INDENT . "\$this->execute('CREATE TYPE enum_%s AS ENUM(%s)');";
    private const DROP_ENUM = self::INDENT . "\$this->execute('DROP TYPE enum_%s');";
    private const ADD_UNIQUE = self::INDENT . "\$this->createIndex('%s', '%s', '%s', true);";
    private const DROP_INDEX = self::INDENT . "\$this->dropIndex('%s', '%s');";

    /**
     * @var \yii\db\Connection
     */
    private $db;

    /**
     * @var \cebe\yii2openapi\lib\items\DbModel
     */
    private $model;

    /**
     * @var \yii\db\TableSchema|null
     */
    private $tableSchema;

    /**@var bool */
    private $isPostgres;

    /**@var bool */
    private $isMysql;

    /**
     * @var MigrationModel $migration
     **/
    private $migration;

    /**
     * @var array<int, string>
     */
    private $uniqueColumns;

    /**
     * @var array<int, string>
     */
    private $currentUniqueColumns;

    /**
     * @var \yii\db\ColumnSchema[]
     */
    private $newColumns;

    /**
     * @var \yii\db\Schema
     */
    private $dbSchema;

    public function __construct(Connection $db, DbModel $model)
    {
        $this->db = $db;
        $this->model = $model;
        $this->tableSchema = $db->getTableSchema($model->getTableAlias(), true);
        $this->dbSchema = $db->getSchema();
        $this->isPostgres = $this->db->getDriverName() === 'pgsql';
        $this->isMysql = $this->db->getDriverName() === 'mysql';
    }

    public function build():MigrationModel
    {
        return $this->tableSchema === null ? $this->buildFresh() : $this->buildSecondary();
    }

    public function buildFresh():MigrationModel
    {
        $this->migration = new MigrationModel($this->model, true);
        $this->uniqueColumns = $this->model->getUniqueColumnsList();
        $this->newColumns = $this->model->attributesToColumnSchema();
        $tableName = $this->model->getTableAlias();
        $codeColumns = VarDumper::export(array_map(
            function (ColumnSchema $column) {
                $isUnique = in_array($column->name, $this->uniqueColumns, true);
                return $this->columnToCode($column, $isUnique, false);
            },
            $this->model->attributesToColumnSchema()
        ));
        $codeColumns = str_replace(PHP_EOL, PHP_EOL . self::INDENT, $codeColumns);
        $this->migration->addUpCode(sprintf(self::ADD_TABLE, $this->model->getTableAlias(), $codeColumns))
                        ->addDownCode(sprintf(self::DROP_TABLE, $this->model->getTableAlias()));
        if ($this->isPostgres) {
            $enums = $this->model->getEnumAttributes();
            foreach ($enums as $attr) {
                $items = ColumnToCode::enumToString($attr->enumValues);
                $this->migration->addUpCode(sprintf(self::ADD_ENUM, $attr->columnName, $items), true)
                                ->addDownCode(sprintf(self::DROP_ENUM, $attr->columnName));
            }
        }
        foreach ($this->model->getHasOneRelations() as $relation) {
            $fkCol = $relation->getColumnName();
            $refCol = $relation->getForeignName();
            $refTable = $relation->getTableAlias();
            $fkName = $this->foreignKeyName($this->model->tableName, $fkCol, $relation->getTableName(), $refCol);
            $this->migration->addUpCode(sprintf(self::ADD_FK, $fkName, $tableName, $fkCol, $refTable, $refCol))
                            ->addDownCode(sprintf(self::DROP_FK, $fkName, $tableName));
            if ($relation->getTableName() !== $this->model->tableName) {
                $this->migration->dependencies[] = $refTable;
            }
        }

        return $this->migration;
    }

    public function buildSecondary():MigrationModel
    {
        $this->migration = new MigrationModel($this->model, false);
        $this->uniqueColumns = $this->model->getUniqueColumnsList();
        $this->currentUniqueColumns = array_values($this->findUniqueIndexes());
        $this->newColumns = $this->model->attributesToColumnSchema();
        $wantNames = array_keys($this->newColumns);
        $haveNames = $this->tableSchema->columnNames;
        sort($wantNames);
        sort($haveNames);
        $columnsForCreate = array_map(
            function (string $missingColumn) {
                return $this->newColumns[$missingColumn];
            },
            array_diff($wantNames, $haveNames)
        );

        $columnsForDrop = array_map(
            function (string $unknownColumn) {
                return $this->tableSchema->columns[$unknownColumn];
            },
            array_diff($haveNames, $wantNames)
        );

        $columnsForChange = array_intersect($wantNames, $haveNames);

        $this->buildColumnsCreation($columnsForCreate);
        $this->buildColumnsDrop($columnsForDrop);
        foreach ($columnsForChange as $commonColumn) {
            $this->buildColumnChanges($this->tableSchema->columns[$commonColumn], $this->newColumns[$commonColumn]);
        }

        $this->buildRelations();
        return $this->migration;
    }

    /**
     * @param array|\yii\db\ColumnSchema[] $columns
     */
    private function buildColumnsCreation(array $columns):void
    {
        foreach ($columns as $column) {
            if ($column->isPrimaryKey) {
                // TODO: Avoid pk changes, or previous pk should be dropped before
            }
            $isUnique = in_array($column->name, $this->uniqueColumns, true);
            $columnCode = ColumnToCode::wrapQuotesOnlyRaw($this->columnToCode($column, $isUnique, false));
            $tableName = $this->model->getTableAlias();
            $this->migration->addUpCode(sprintf(self::ADD_COLUMN, $tableName, $column->name, $columnCode))
                            ->addDownCode(sprintf(self::DROP_COLUMN, $tableName, $column->name));
            if ($this->isPostgres && $column->dbType === 'enum') {
                $items = ColumnToCode::enumToString($column->enumValues);
                $this->migration->addUpCode(sprintf(self::ADD_ENUM, $column->name, $items), true)
                                ->addDownCode(sprintf(self::DROP_ENUM, $column->name));
            }
        }
    }

    /**
     * @param array|\yii\db\ColumnSchema[] $columns
     */
    private function buildColumnsDrop(array $columns):void
    {
        foreach ($columns as $column) {
            if ($column->isPrimaryKey) {
                // TODO: drop pk index and foreign keys before or avoid drop
            }
            $isUnique = in_array($column->name, $this->currentUniqueColumns, true);
            $columnCode = $this->columnToCode($column, $isUnique, true);
            $tableName = $this->model->getTableAlias();
            $this->migration->addDownCode(sprintf(self::ADD_COLUMN, $tableName, $column->name, $columnCode))
                            ->addUpCode(sprintf(self::DROP_COLUMN, $tableName, $column->name));
            if ($this->isPostgres && $column->dbType === 'enum') {
                $items = ColumnToCode::enumToString($column->enumValues);
                $this->migration->addDownCode(sprintf(self::ADD_ENUM, $column->name, $items, true))
                                ->addUpCode(sprintf(self::DROP_ENUM, $column->name), true);
            }
        }
    }

    private function buildColumnChanges(ColumnSchema $current, ColumnSchema $desired):void
    {
        $isUniqueCurrent = in_array($current->name, $this->currentUniqueColumns, true);
        $isUniqueDesired = in_array($desired->name, $this->uniqueColumns, true);
        if ($current->isPrimaryKey || in_array($desired->dbType, ['pk', 'upk', 'bigpk', 'ubigpk'])) {
            // do not adjust existing primary keys
            return;
        }
        $columnName = $current->name;
        $tableName = $this->model->getTableAlias();
        if ($isUniqueCurrent !== $isUniqueDesired) {
            $addUnique = sprintf(self::ADD_UNIQUE, 'unique_' . $columnName, $tableName, $columnName);
            $dropUnique = sprintf(self::DROP_INDEX, 'unique_' . $columnName, $tableName);
            $this->migration->addUpCode($isUniqueDesired === true ? $addUnique : $dropUnique)
                            ->addDownCode($isUniqueDesired === true ? $dropUnique : $addUnique);
        }
        $changedAttributes = $this->compareColumns($current, $desired);
        if (empty($changedAttributes)) {
            return;
        }
        if ($this->isPostgres) {
            $this->buildColumnsChangePostgres($current, $desired, $changedAttributes);
            return;
        }
        $newColumn = clone $current;
        foreach ($changedAttributes as $attr) {
            $newColumn->$attr = $desired->$attr;
        }
        if (!empty($newColumn->enumValues)) {
            $newColumn->dbType = 'enum';
        }
        $upCode = $this->columnToCode($newColumn, false, true); //unique marks solved in separated queries
        $downCode = $this->columnToCode($current, false, true);
        $upCode = ColumnToCode::wrapQuotesOnlyRaw($upCode);
        $downCode = ColumnToCode::wrapQuotesOnlyRaw($downCode);
        $this->migration->addUpCode(sprintf(self::ALTER_COLUMN, $tableName, $columnName, $upCode))
                        ->addDownCode(sprintf(self::ALTER_COLUMN, $tableName, $columnName, $downCode));
    }

    private function buildColumnsChangePostgres(ColumnSchema $current, ColumnSchema $desired, array $changes):void
    {
        $tableName = $this->model->getTableAlias();
        $columnName = $desired->name;
        $upCodes = $downCodes = [];
        $isChangeToEnum = $current->type !== $desired->type && !empty($desired->enumValues);
        $isChangeFromEnum = $current->type !== $desired->type && !empty($current->enumValues);
        if ($isChangeToEnum) {
            $items = ColumnToCode::enumToString($desired->enumValues);
            $this->migration->addUpCode(sprintf(self::ADD_ENUM, $desired->name, $items), true);
        }
        if ($isChangeFromEnum) {
            $this->migration->addUpCode(sprintf(self::DROP_ENUM, $current->name));
        }
        if (!empty(array_intersect(['type', 'size'], $changes))) {
            $upCodes[] = (new ColumnToCode($this->dbSchema, $desired, false))->resolveTypeOnly();
            $downCodes[] = (new ColumnToCode($this->dbSchema, $current, false, true))->resolveTypeOnly();
        }
        if (in_array('allowNull', $changes, true)) {
            $upCodes[] = $desired->allowNull === true ? 'DROP NOT NULL' : 'SET NOT NULL';
            $downCodes[] = $desired->allowNull === true ? 'SET NOT NULL' : 'DROP NOT NULL';
        }
        if (in_array('defaultValue', $changes, true)) {
            $upCodes[] = $desired->defaultValue !== null
                ? 'SET ' . (new ColumnToCode($this->dbSchema, $desired, false))->resolveDefaultOnly()
                : 'DROP DEFAULT';
            $downCodes[] = $current->defaultValue !== null
                ? 'SET ' . (new ColumnToCode($this->dbSchema, $current, false))->resolveDefaultOnly()
                : 'DROP DEFAULT';
        }
        foreach ($upCodes as $upCode) {
            $upCode = ColumnToCode::wrapQuotesOnlyRaw($upCode);
            $this->migration->addUpCode(sprintf(self::ALTER_COLUMN, $tableName, $columnName, $upCode));
        }
        foreach ($downCodes as $downCode) {
            $downCode = ColumnToCode::wrapQuotesOnlyRaw($downCode);
            $this->migration->addDownCode(sprintf(self::ALTER_COLUMN, $tableName, $columnName, $downCode), true);
        }
        if ($isChangeFromEnum) {
            $items = ColumnToCode::enumToString($current->enumValues);
            $this->migration->addDownCode(sprintf(self::ADD_ENUM, $current->name, $items), true);
        }
        if ($isChangeToEnum) {
            $this->migration->addDownCode(sprintf(self::DROP_ENUM, $desired->name), true);
        }
    }

    private function buildRelations():void
    {
        $tableName = $this->model->getTableAlias();
        if (empty($this->model->relations)) {
            //? Revert existed relations
            foreach ($this->tableSchema->foreignKeys as $relation) {
                $refTable = array_shift($relation);
                $refCol = array_keys($relation)[0];
                $fkCol = $relation[$refCol];
                $fkName = $this->foreignKeyName($this->model->tableName, $fkCol, $refTable, $refCol);
                $this->migration->addUpCode(sprintf(self::DROP_FK, $fkName, $tableName), true)
                                ->addDownCode(sprintf(self::ADD_FK, $fkName, $tableName, $fkCol, $refTable, $refCol));
                if ($refTable !== $this->model->tableName) {
                    $this->migration->dependencies[$refTable];
                }
            }
        }
        foreach ($this->model->getHasOneRelations() as $relation) {
            $fkCol = $relation->getColumnName();
            $refCol = $relation->getForeignName();
            $refTable = $relation->getTableAlias();
            $fkName = $this->foreignKeyName($this->model->tableName, $fkCol, $relation->getTableName(), $refCol);
            if (isset($tableSchema->foreignKeys[$fkName])) {
                continue;
            }
            $this->migration->addUpCode(sprintf(self::ADD_FK, $fkName, $tableName, $fkCol, $refTable, $refCol))
                            ->addDownCode(sprintf(self::DROP_FK, $fkName, $tableName));
            if ($relation->getTableName() !== $this->model->tableName) {
                $this->migration->dependencies[] = $refTable;
            }
        }
    }

    private function findUniqueIndexes():array
    {
        try {
            return $this->db->getSchema()->findUniqueIndexes($this->tableSchema);
        } catch (NotSupportedException $e) {
            return [];
        }
    }

    private function columnToCode(ColumnSchema $column, bool $isUnique, bool $fromDb = false):string
    {
        return (new ColumnToCode($this->dbSchema, $column, $isUnique, $fromDb))->resolve();
    }

    private function foreignKeyName(string $table, string $column, string $foreignTable, string $foreignColumn):string
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

    private function compareColumns(ColumnSchema $current, ColumnSchema $desired)
    {
        $changedAttributes = [];
        $isMysqlBoolean = $this->isMysql && $current->dbType === 'tinyint(1)' && $desired->type === 'boolean';
        if ($isMysqlBoolean) {
            if (\is_bool($desired->defaultValue) || \is_string($desired->defaultValue)) {
                $desired->defaultValue = (int) $desired->defaultValue;
            }
            if ($current->defaultValue !== $desired->defaultValue) {
                $changedAttributes[] = 'defaultValue';
            }
            if ($current->allowNull !== $desired->allowNull) {
                $changedAttributes[] = 'allowNull';
            }
            return $changedAttributes;
        }
        if ($current->phpType === 'integer' && $current->defaultValue !== null) {
            $current->defaultValue = (int) $current->defaultValue;
        }
        foreach (['type', 'size', 'allowNull', 'defaultValue', 'enumValues'] as $attr) {
            if ($current->$attr !== $desired->$attr) {
                $changedAttributes[] = $attr;
            }
        }
        return $changedAttributes;
    }
}
