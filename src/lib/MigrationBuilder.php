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
use yii\db\Schema;
use function array_intersect;
use function array_map;
use function in_array;
use function is_bool;
use function is_string;
use function str_replace;

class MigrationBuilder
{

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
     * @var \cebe\yii2openapi\lib\MigrationRecordBuilder
     */
    private $recordBuilder;

    /**
     * MigrationBuilder constructor.
     * @param \yii\db\Connection                  $db
     * @param \cebe\yii2openapi\lib\items\DbModel $model
     * @throws \yii\base\NotSupportedException
     */
    public function __construct(Connection $db, DbModel $model)
    {
        $this->db = $db;
        $this->model = $model;
        $this->tableSchema = $db->getTableSchema($model->getTableAlias(), true);
        $this->isPostgres = $this->db->getDriverName() === 'pgsql';
        $this->isMysql = $this->db->getDriverName() === 'mysql';
        $this->recordBuilder = new MigrationRecordBuilder($db->getSchema());
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
        if (empty($this->newColumns)) {
            return $this->migration;
        }
        $builder = $this->recordBuilder;
        $tableName = $this->model->getTableAlias();

        $this->migration->addUpCode($builder->createTable($tableName, $this->newColumns, $this->uniqueColumns))
                        ->addDownCode($builder->dropTable($tableName));
        if ($this->isPostgres) {
            $enums = $this->model->getEnumAttributes();
            foreach ($enums as $attr) {
                $this->migration->addUpCode($builder->createEnum($attr->columnName, $attr->enumValues), true)
                                ->addDownCode($builder->dropEnum($attr->columnName));
            }
        }
        foreach ($this->model->getHasOneRelations() as $relation) {
            $fkCol = $relation->getColumnName();
            $refCol = $relation->getForeignName();
            $refTable = $relation->getTableAlias();
            $fkName = $this->foreignKeyName($this->model->tableName, $fkCol, $relation->getTableName(), $refCol);
            $this->migration->addUpCode($builder->addFk($fkName, $tableName, $fkCol, $refTable, $refCol))
                            ->addDownCode($builder->dropFk($fkName, $tableName));
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
            $tableName = $this->model->getTableAlias();
            $this->migration->addUpCode($this->recordBuilder->addColumn($tableName, $column, $isUnique))
                            ->addDownCode($this->recordBuilder->dropColumn($tableName, $column->name));
            if ($this->isPostgres && $column->dbType === 'enum') {
                $this->migration->addUpCode($this->recordBuilder->createEnum($column->name, $column->enumValues), true)
                                ->addDownCode($this->recordBuilder->dropEnum($column->name), true);
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
                // TODO: drop pk index and foreign keys before or avoid drop?
            }
            $isUnique = in_array($column->name, $this->currentUniqueColumns, true);
            $tableName = $this->model->getTableAlias();
            $this->migration->addDownCode($this->recordBuilder->addDbColumn($tableName, $column, $isUnique))
                            ->addUpCode($this->recordBuilder->dropColumn($tableName, $column->name));
            if ($this->isPostgres && $column->dbType === 'enum') {
                $this->migration->addDownCode($this->recordBuilder->createEnum($column->name, $column->enumValues))
                                ->addUpCode($this->recordBuilder->dropEnum($column->name), true);
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
        $changedAttributes = $this->compareColumns($current, $desired);
        if (empty($changedAttributes)) {
            return;
        }
        if ($this->isPostgres) {
            $this->buildColumnsChangePostgres($current, $desired, $changedAttributes);
            $this->addUniqueIndex($isUniqueCurrent, $isUniqueDesired, $tableName, $columnName);
            return;
        }
        $newColumn = clone $current;
        foreach ($changedAttributes as $attr) {
            $newColumn->$attr = $desired->$attr;
        }
        if (!empty($newColumn->enumValues)) {
            $newColumn->dbType = 'enum';
        }
        $this->migration->addUpCode($this->recordBuilder->alterColumn($tableName, $newColumn))
                        ->addDownCode($this->recordBuilder->alterColumn($tableName, $current));
        $this->addUniqueIndex($isUniqueCurrent, $isUniqueDesired, $tableName, $columnName);
    }

    private function buildColumnsChangePostgres(ColumnSchema $current, ColumnSchema $desired, array $changes):void
    {
        $tableName = $this->model->getTableAlias();
        $isChangeToEnum = $current->type !== $desired->type && !empty($desired->enumValues);
        $isChangeFromEnum = $current->type !== $desired->type && !empty($current->enumValues);
        $isChangedEnum = $current->type === $desired->type && !empty($current->enumValues);
        if ($isChangedEnum) {
            // Generation for change enum values not supported. Do it manually
            // This action require several steps and can't be applied during single transaction
            return;
        }
        if ($isChangeToEnum) {
            $this->migration->addUpCode($this->recordBuilder->createEnum($desired->name, $desired->enumValues), true);
        }
        if ($isChangeFromEnum) {
            $this->migration->addUpCode($this->recordBuilder->dropEnum($current->name));
        }
        if (!empty(array_intersect(['type', 'size'], $changes))) {
            $this->migration->addUpCode($this->recordBuilder->alterColumnType($tableName, $desired));
            $this->migration->addDownCode($this->recordBuilder->alterColumnTypeFromDb($tableName, $current));
        }
        if (in_array('allowNull', $changes, true)) {
            if ($desired->allowNull === true) {
                $this->migration->addUpCode($this->recordBuilder->dropColumnNotNull($tableName, $desired));
                $this->migration->addDownCode($this->recordBuilder->setColumnNotNull($tableName, $current), true);
            } else {
                $this->migration->addUpCode($this->recordBuilder->setColumnNotNull($tableName, $desired));
                $this->migration->addDownCode($this->recordBuilder->dropColumnNotNull($tableName, $current), true);
            }
        }
        if (in_array('defaultValue', $changes, true)) {
            $upCode = $desired->defaultValue === null
                ? $this->recordBuilder->dropColumnDefault($tableName, $desired)
                : $this->recordBuilder->setColumnDefault($tableName, $desired);
            $downCode = $current->defaultValue === null
                ? $this->recordBuilder->dropColumnDefault($tableName, $current)
                : $this->recordBuilder->setColumnDefaultFromDb($tableName, $current);
            if ($upCode && $downCode) {
                $this->migration->addUpCode($upCode)->addDownCode($downCode, true);
            }
        }
        if ($isChangeFromEnum) {
            $this->migration
                ->addDownCode($this->recordBuilder->createEnum($current->name, $current->enumValues), true);
        }
        if ($isChangeToEnum) {
            $this->migration->addDownCode($this->recordBuilder->dropEnum($current->name), true);
        }
    }

    private function buildRelations():void
    {
        $tableName = $this->model->getTableAlias();
        $existedRelations = [];
        foreach ($this->tableSchema->foreignKeys as $fkName => $relation) {
            $refTable = $this->unPrefixTableName(array_shift($relation));
            $refCol = array_keys($relation)[0];
            $fkCol = $relation[$refCol];
            $existedRelations[$fkName] = ['refTable' => $refTable, 'refCol' => $refCol, 'fkCol' => $fkCol];
        }
        foreach ($this->model->getHasOneRelations() as $relation) {
            $fkCol = $relation->getColumnName();
            $refCol = $relation->getForeignName();
            $refTable = $relation->getTableAlias();
            $fkName = $this->foreignKeyName($this->model->tableName, $fkCol, $relation->getTableName(), $refCol);
            if (isset($existedRelations[$fkName])) {
                unset($existedRelations[$fkName]);
                continue;
            }
            $this->migration
                ->addUpCode($this->recordBuilder->addFk($fkName, $tableName, $fkCol, $refTable, $refCol))
                ->addDownCode($this->recordBuilder->dropFk($fkName, $tableName));
            if ($relation->getTableName() !== $this->model->tableName) {
                $this->migration->dependencies[] = $refTable;
            }
        }
        foreach ($existedRelations as $fkName => $relation) {
            ['fkCol' => $fkCol, 'refCol' => $refCol, 'refTable' => $refTable] = $relation;
            $this->migration
                ->addUpCode($this->recordBuilder->dropFk($fkName, $tableName), true)
                ->addDownCode($this->recordBuilder->addFk($fkName, $tableName, $fkCol, $refTable, $refCol), true);
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

    private function foreignKeyName(string $table, string $column, string $foreignTable, string $foreignColumn):string
    {
        $table = $this->normalizeTableName($table);
        $foreignTable = $this->normalizeTableName($foreignTable);
        return "fk_{$table}_{$column}_{$foreignTable}_{$foreignColumn}";
    }

    private function normalizeTableName(string $tableName):string
    {
        if (preg_match('~^{{%?(.*)}}$~', $tableName, $m)) {
            return $m[1];
        }
        return $tableName;
    }

    private function unPrefixTableName(string $tableName):string
    {
        return str_replace($this->db->tablePrefix, '', $tableName);
    }

    private function compareColumns(ColumnSchema $current, ColumnSchema $desired)
    {
        $changedAttributes = [];
        $isMysqlBoolean = $this->isMysql && $current->dbType === 'tinyint(1)' && $desired->type === 'boolean';
        if ($isMysqlBoolean) {
            if (is_bool($desired->defaultValue) || is_string($desired->defaultValue)) {
                $desired->defaultValue = (int)$desired->defaultValue;
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
            $current->defaultValue = (int)$current->defaultValue;
        }
        if ($desired->phpType === 'int' && $desired->defaultValue !== null) {
            $desired->defaultValue = (int)$desired->defaultValue;
        }
        if ($current->type === $desired->type && !$desired->size && $this->isDbDefaultSize($current)) {
            $desired->size = $current->size;
        }
        foreach (['type', 'size', 'allowNull', 'defaultValue', 'enumValues'] as $attr) {
            if ($current->$attr !== $desired->$attr) {
                $changedAttributes[] = $attr;
            }
        }
        return $changedAttributes;
    }

    /**
     * @param bool   $isUniqueCurrent
     * @param bool   $isUniqueDesired
     * @param string $tableName
     * @param string $columnName
     */
    private function addUniqueIndex(
        bool $isUniqueCurrent,
        bool $isUniqueDesired,
        string $tableName,
        string $columnName
    ):void {
        if ($isUniqueCurrent !== $isUniqueDesired) {
            $addUnique = $this->recordBuilder->addUniqueIndex($tableName, $columnName);
            $dropUnique = $this->recordBuilder->dropUniqueIndex($tableName, $columnName);
            $this->migration->addUpCode($isUniqueDesired === true ? $addUnique : $dropUnique)
                            ->addDownCode($isUniqueDesired === true ? $dropUnique : $addUnique);
        }
    }

    private function isDbDefaultSize(ColumnSchema $current)
    {
        $defaults = [];
        if ($this->isPostgres) {
            $defaults = ['char' => 1, 'string' => 255];
        } elseif ($this->isMysql) {
            $defaults = [
                Schema::TYPE_PK => 11,
                Schema::TYPE_BIGPK => 20,
                Schema::TYPE_CHAR => 1,
                Schema::TYPE_STRING => 255,
                Schema::TYPE_TINYINT => 3,
                Schema::TYPE_SMALLINT => 6,
                Schema::TYPE_INTEGER => 11,
                Schema::TYPE_BIGINT => 20,
                Schema::TYPE_DECIMAL => 10,
                Schema::TYPE_BOOLEAN => 1,
                Schema::TYPE_MONEY => 19,
            ];
        }
        return isset($defaults[$current->type]);
    }
}
