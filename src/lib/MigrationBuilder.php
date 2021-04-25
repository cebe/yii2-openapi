<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\ManyToManyRelation;
use cebe\yii2openapi\lib\items\MigrationModel;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\Connection;
use yii\db\IndexConstraint;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use function array_column;
use function array_diff;
use function array_intersect;
use function array_keys;
use function array_map;
use function in_array;
use function is_bool;
use function is_string;
use function str_replace;
use function substr;

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

    public function buildJunction(ManyToManyRelation $relation):MigrationModel
    {
        $this->tableSchema = $this->db->getTableSchema($relation->getViaTableAlias(), true);
        if ($this->tableSchema !== null) {
            return $this->buildSecondary($relation);
        }
        $this->migration = new MigrationModel($this->model, true, $relation);
        $builder = $this->recordBuilder;
        $tableAlias = $relation->getViaTableAlias();
        $this->migration->addUpCode($builder->createTable($tableAlias, $relation->columnSchema))
                        ->addDownCode($builder->dropTable($tableAlias));
        $this->migration->addUpCode($builder->addPrimaryKey($tableAlias, array_keys($relation->columnSchema)))
                        ->addDownCode($builder->dropPrimaryKey($tableAlias, array_keys($relation->columnSchema)));
        foreach ($relation->getRelations() as $rel) {
            $fkCol = $rel->getColumnName();
            $refCol = $rel->getForeignName();
            $refTable = $rel->getTableAlias();
            $fkName = $this->foreignKeyName($relation->viaTableName, $fkCol, $rel->getTableName(), $refCol);
            if (isset($existedRelations[$fkName])) {
                unset($existedRelations[$fkName]);
                continue;
            }
            $this->migration
                ->addUpCode($this->recordBuilder->addFk($fkName, $tableAlias, $fkCol, $refTable, $refCol))
                ->addDownCode($this->recordBuilder->dropFk($fkName, $tableAlias));
            $this->migration->dependencies[] = $refTable;
        }
        return $this->migration;
    }

    public function buildFresh():MigrationModel
    {
        $this->migration = new MigrationModel($this->model, true);
        $this->newColumns = $this->model->attributesToColumnSchema();
        if (empty($this->newColumns)) {
            return $this->migration;
        }
        $builder = $this->recordBuilder;
        $tableName = $this->model->getTableAlias();

        $this->migration->addUpCode($builder->createTable($tableName, $this->newColumns))
                        ->addDownCode($builder->dropTable($tableName));
        if ($this->isPostgres) {
            $enums = $this->model->getEnumAttributes();
            foreach ($enums as $attr) {
                $this->migration->addUpCode($builder->createEnum($attr->columnName, $attr->enumValues), true)
                                ->addDownCode($builder->dropEnum($attr->columnName));
            }
        }
        if (!empty($this->model->junctionCols)) {
            if (!isset($this->model->attributes[$this->model->pkName])) {
                $this->migration->addUpCode($builder->addPrimaryKey($tableName, $this->model->junctionCols))
                                ->addDownCode($builder->dropPrimaryKey($tableName, $this->model->junctionCols));
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

        foreach ($this->model->indexes as $index) {
            $upCode = $index->isUnique ? $builder->addUniqueIndex($tableName, $index->name, $index->columns)
                                       : $builder->addIndex($tableName, $index->name, $index->columns, $index->type);
            $this->migration->addUpCode($upCode)
                            ->addDownCode($builder->dropIndex($tableName, $index->name));
        }

        return $this->migration;
    }

    public function buildSecondary(?ManyToManyRelation $relation = null):MigrationModel
    {
        $this->migration = new MigrationModel($this->model, false, $relation);
        $this->newColumns = $relation ? $relation->columnSchema : $this->model->attributesToColumnSchema();
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
        if ($this->model->junctionCols && !isset($this->model->attributes[$this->model->pkName])) {
            if (!empty(array_intersect($columnsForDrop, $this->model->junctionCols))) {
                $builder = $this->recordBuilder;
                $tableName = $this->model->getTableAlias();
                $this->migration->addUpCode($builder->dropPrimaryKey($tableName, $this->model->junctionCols))
                                ->addDownCode($builder->addPrimaryKey($tableName, $this->model->junctionCols));
            }
        }
        $this->buildColumnsDrop($columnsForDrop);
        foreach ($columnsForChange as $commonColumn) {
            $this->buildColumnChanges($this->tableSchema->columns[$commonColumn], $this->newColumns[$commonColumn]);
        }
        if ($relation) {
            $this->buildRelationsForJunction($relation);
        } else {
            $this->buildRelations();
        }
        $this->buildIndexChanges();
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
            $tableName = $this->model->getTableAlias();
            $this->migration->addUpCode($this->recordBuilder->addColumn($tableName, $column))
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
            $tableName = $this->model->getTableAlias();
            $this->migration->addDownCode($this->recordBuilder->addDbColumn($tableName, $column))
                            ->addUpCode($this->recordBuilder->dropColumn($tableName, $column->name));
            if ($this->isPostgres && $column->dbType === 'enum') {
                $this->migration->addDownCode($this->recordBuilder->createEnum($column->name, $column->enumValues))
                                ->addUpCode($this->recordBuilder->dropEnum($column->name), true);
            }
        }
    }

    private function buildColumnChanges(ColumnSchema $current, ColumnSchema $desired):void
    {
        if ($current->isPrimaryKey || in_array($desired->dbType, ['pk', 'upk', 'bigpk', 'ubigpk'])) {
            // do not adjust existing primary keys
            return;
        }
        $tableName = $this->model->getTableAlias();
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
        $this->migration->addUpCode($this->recordBuilder->alterColumn($tableName, $newColumn))
                        ->addDownCode($this->recordBuilder->alterColumn($tableName, $current));
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
            $addUsing = $this->isNeedUsingExpression($desired->type, $current->type);
            $this->migration->addUpCode($this->recordBuilder->alterColumnType($tableName, $desired));
            $this->migration->addDownCode($this->recordBuilder->alterColumnTypeFromDb($tableName, $current, $addUsing));
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

    private function isNeedUsingExpression(string $fromType, string $toType): bool
    {
        $strings = ['string', 'text', 'char'];
        if (in_array($fromType, $strings) && in_array($toType, $strings)) {
            return false;
        }
        $ints = ['smallint', 'integer', 'bigint', 'float', 'decimal'];
        if (in_array($fromType, $ints) && in_array($toType, $ints)) {
            return false;
        }
        $dates = ['date', 'timestamp'];
        if (in_array($fromType, $dates) && in_array($toType, $dates)) {
            return false;
        }
        return true;
    }

    private function buildRelationsForJunction(ManyToManyRelation $relation):void
    {
        $tableAlias = $relation->viaTableAlias;
        $existedRelations = [];
        foreach ($this->tableSchema->foreignKeys as $fkName => $rel) {
            $refTable = $this->unPrefixTableName(array_shift($rel));
            $refCol = array_keys($rel)[0];
            $fkCol = $rel[$refCol];
            $existedRelations[$fkName] = ['refTable' => $refTable, 'refCol' => $refCol, 'fkCol' => $fkCol];
        }
        foreach ($relation->getRelations() as $rel) {
            $fkCol = $rel->getColumnName();
            $refCol = $rel->getForeignName();
            $refTable = $rel->getTableAlias();
            $fkName = $this->foreignKeyName($relation->viaTableName, $fkCol, $rel->getTableName(), $refCol);
            if (isset($existedRelations[$fkName])) {
                unset($existedRelations[$fkName]);
                continue;
            }
            $this->migration
                ->addUpCode($this->recordBuilder->addFk($fkName, $tableAlias, $fkCol, $refTable, $refCol))
                ->addDownCode($this->recordBuilder->dropFk($fkName, $tableAlias));
            if ($rel->getTableName() !== $relation->viaTableName) {
                $this->migration->dependencies[] = $refTable;
            }
        }
        foreach ($existedRelations as $fkName => $rel) {
            ['fkCol' => $fkCol, 'refCol' => $refCol, 'refTable' => $refTable] = $rel;
            $this->migration
                ->addUpCode($this->recordBuilder->dropFk($fkName, $tableAlias), true)
                ->addDownCode($this->recordBuilder->addFk($fkName, $tableAlias, $fkCol, $refTable, $refCol), true);
        }
    }

    private function buildRelations():void
    {
        $tableAlias = $this->model->getTableAlias();
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
                ->addUpCode($this->recordBuilder->addFk($fkName, $tableAlias, $fkCol, $refTable, $refCol))
                ->addDownCode($this->recordBuilder->dropFk($fkName, $tableAlias));
            if ($relation->getTableName() !== $this->model->tableName) {
                $this->migration->dependencies[] = $refTable;
            }
        }
        foreach ($existedRelations as $fkName => $relation) {
            ['fkCol' => $fkCol, 'refCol' => $refCol, 'refTable' => $refTable] = $relation;
            $this->migration
                ->addUpCode($this->recordBuilder->dropFk($fkName, $tableAlias), true)
                ->addDownCode($this->recordBuilder->addFk($fkName, $tableAlias, $fkCol, $refTable, $refCol), true);
        }
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\DbIndex[]
     */
    private function findTableIndexes():array
    {
        if ($this->isPostgres) {
            return $this->findPostgresTableIndexes();
        }
        $dbIndexes = [];
        try {
            /**@var IndexConstraint[] $indexes */
            $indexes = $this->db->getSchema()->getTableIndexes($this->tableSchema->name);
            $fkIndexes = array_keys($this->tableSchema->foreignKeys);
            foreach ($indexes as $index) {
                if (!$index->isPrimary && !in_array($index->name, $fkIndexes, true)) {
                    $dbIndexes[] = DbIndex::fromConstraint($this->model->tableName, $index);
                }
            }
            return ArrayHelper::index($dbIndexes, 'name');
        } catch (NotSupportedException $e) {
            return [];
        }
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\DbIndex[]
     */
    private function findPostgresTableIndexes():array
    {
        static $sql = <<<'SQL'
SELECT
    "ic"."relname" AS "name",
    "ia"."attname" AS "column_name",
    "i"."indisunique" AS "index_is_unique",
    "i"."indisprimary" AS "index_is_primary",
    "it"."amname"  AS "index_type"
FROM "pg_class" AS "tc"
INNER JOIN "pg_namespace" AS "tcns"
    ON "tcns"."oid" = "tc"."relnamespace"
INNER JOIN "pg_index" AS "i"
    ON "i"."indrelid" = "tc"."oid"
INNER JOIN "pg_class" AS "ic"
    ON "ic"."oid" = "i"."indexrelid"
INNER JOIN "pg_attribute" AS "ia"
    ON "ia"."attrelid" = "i"."indexrelid"
INNER JOIN pg_am it on it.oid = ic.relam
WHERE "tcns"."nspname" = :schemaName AND "tc"."relname" = :tableName
ORDER BY "ia"."attnum" ASC
SQL;
        $indexes = $this->db->createCommand($sql, [
            ':schemaName' => $this->db->getSchema()->defaultSchema,
            ':tableName' => $this->db->tablePrefix.$this->model->tableName
        ])->queryAll();
        $indexes = ArrayHelper::index($indexes, null, 'name');

        $dbIndexes = [];
        foreach ($indexes as $name => $index) {
            if ((bool) $index[0]['index_is_primary']) {
                continue;
            }
            $dbIndex = DbIndex::make(
                $this->model->tableName,
                ArrayHelper::getColumn($index, 'column_name'),
                $index[0]['index_type'] === 'btree' ? null : $index[0]['index_type'],
                (bool) $index[0]['index_is_unique']
            );
            $dbIndexes[$dbIndex->name] = $dbIndex;
        }
        return $dbIndexes;
    }

    private function foreignKeyName(string $table, string $column, string $foreignTable, string $foreignColumn):string
    {
        $table = $this->normalizeTableName($table);
        $foreignTable = $this->normalizeTableName($foreignTable);
        return substr("fk_{$table}_{$column}_{$foreignTable}_{$foreignColumn}", 0, 63);
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

    private function buildIndexChanges()
    {
        $haveIndexes = $this->findTableIndexes();
        $wantIndexes = $this->model->indexes;
        $wantIndexNames = array_column($wantIndexes, 'name');
        $haveIndexNames = array_column($haveIndexes, 'name');
        $tableName = $this->model->getTableAlias();
        /**@var \cebe\yii2openapi\lib\items\DbIndex[] $forDrop */
        $forDrop = array_map(function ($idx) use ($haveIndexes) {
            return $haveIndexes[$idx];
        }, array_diff($haveIndexNames, $wantIndexNames));
        /**@var \cebe\yii2openapi\lib\items\DbIndex[] $forCreate */
        $forCreate = array_map(function ($idx) use ($wantIndexes) {
            return $wantIndexes[$idx];
        }, array_diff($wantIndexNames, $haveIndexNames));
        $forChange = array_intersect($wantIndexNames, $haveIndexNames);
        foreach ($forChange as $indexName) {
            if ($haveIndexes[$indexName]->isEqual($wantIndexes[$indexName]) === false) {
                $forCreate[] = $wantIndexes[$indexName];
                $forDrop[] = $haveIndexes[$indexName];
            }
        }
        foreach ($forDrop as $index) {
            $downCode = $index->isUnique
                ? $this->recordBuilder->addUniqueIndex($tableName, $index->name, $index->columns)
                : $this->recordBuilder->addIndex($tableName, $index->name, $index->columns, $index->type);
            $this->migration ->addUpCode($this->recordBuilder->dropIndex($tableName, $index->name))
                             ->addDownCode($downCode);
        }
        foreach ($forCreate as $index) {
            $upCode = $index->isUnique
                ? $this->recordBuilder->addUniqueIndex($tableName, $index->name, $index->columns)
                : $this->recordBuilder->addIndex($tableName, $index->name, $index->columns, $index->type);
            $this->migration ->addDownCode($this->recordBuilder->dropIndex($tableName, $index->name))
                             ->addUpCode($upCode);
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
