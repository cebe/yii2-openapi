<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\migrations;

use cebe\yii2openapi\lib\ColumnToCode;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\ManyToManyRelation;
use cebe\yii2openapi\lib\items\MigrationModel;
use Yii;
use yii\db\ColumnSchema;
use yii\helpers\VarDumper;
use yii\db\Connection;

abstract class BaseMigrationBuilder
{
    /**
     * @var \yii\db\Connection
     */
    protected $db;

    /**
     * @var \cebe\yii2openapi\lib\items\DbModel
     */
    protected $model;

    /**
     * @var \yii\db\TableSchema|null
     */
    protected $tableSchema;

    /**
     * @var MigrationModel $migration
     **/
    protected $migration;

    /**
     * @var \yii\db\ColumnSchema[]
     */
    protected $newColumns;

    /**
     * @var \cebe\yii2openapi\lib\migrations\MigrationRecordBuilder
     */
    protected $recordBuilder;

    /**
     * MigrationBuilder constructor.
     * @param \yii\db\Connection                  $db
     * @param \cebe\yii2openapi\lib\items\DbModel $model
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\NotSupportedException
     */
    public function __construct(Connection $db, DbModel $model)
    {
        $this->db = $db;
        $this->model = $model;
        $this->tableSchema = $db->getTableSchema($model->getTableAlias(), true);
        $this->recordBuilder = Yii::createObject(MigrationRecordBuilder::class, [$db->getSchema()]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function build():MigrationModel
    {
        return $this->tableSchema === null ? $this->buildFresh() : $this->buildSecondary();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function buildJunction(ManyToManyRelation $relation):MigrationModel
    {
        $this->tableSchema = $this->db->getTableSchema($relation->getViaTableAlias(), true);
        if ($this->tableSchema !== null) {
            return $this->buildSecondary($relation);
        }
        $this->migration = Yii::createObject(MigrationModel::class, [$this->model, true, $relation, []]);
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

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function buildFresh():MigrationModel
    {
        $this->migration = Yii::createObject(MigrationModel::class, [$this->model, true, null, []]);
        $this->newColumns = $this->model->attributesToColumnSchema();
        if (empty($this->newColumns)) {
            return $this->migration;
        }
        $builder = $this->recordBuilder;
        $tableName = $this->model->getTableAlias();

        $this->migration->addUpCode($builder->createTable($tableName, $this->newColumns))
                        ->addDownCode($builder->dropTable($tableName));
        $nonAutoincrementPk = false;
        foreach ($this->newColumns as $col) {
            if ($col->isPrimaryKey && !$col->autoIncrement) {
                $nonAutoincrementPk = $col;
                break;
            }
        }
        if ($nonAutoincrementPk) {
            $pkName = 'pk_' . $this->model->tableName . '_' . $nonAutoincrementPk->name;
            $this->migration
                ->addUpCode($builder->addPrimaryKey($tableName, [$nonAutoincrementPk->name], $pkName))
                ->addDownCode($builder->dropPrimaryKey($tableName, [$nonAutoincrementPk->name], $pkName));
        }
        $this->createEnumMigrations();
        if (!empty($this->model->junctionCols) && !isset($this->model->attributes[$this->model->pkName])) {
            $this->migration->addUpCode($builder->addPrimaryKey($tableName, $this->model->junctionCols))
                            ->addDownCode($builder->dropPrimaryKey($tableName, $this->model->junctionCols));
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

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function buildSecondary(?ManyToManyRelation $relation = null):MigrationModel
    {
        $this->migration = Yii::createObject(MigrationModel::class, [$this->model, false, $relation, []]);
        $this->newColumns = $relation->columnSchema ?? $this->model->attributesToColumnSchema();
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
            $current = $this->tableSchema->columns[$commonColumn];
            $desired = $this->newColumns[$commonColumn];
            if ($current->isPrimaryKey || in_array($desired->dbType, ['pk', 'upk', 'bigpk', 'ubigpk'])) {
                // do not adjust existing primary keys
                continue;
            }
            if (!empty($current->enumValues)) {
                $current->type = 'enum';
                $current->dbType = 'enum';
            }
            if (!empty($desired->enumValues)) {
                $desired->type = 'enum';
                $desired->dbType = 'enum';
            }
            $changedAttributes = $this->compareColumns($current, $desired);
            if (empty($changedAttributes)) {
                continue;
            }
            $this->buildColumnChanges($current, $desired, $changedAttributes);
        }
        if ($relation) {
            $this->buildRelationsForJunction($relation);
        } else {
            $this->buildRelations();
        }
        if (!$relation) {
            $this->buildIndexChanges();
        }
        return $this->migration;
    }

    /**
     * @param array|ColumnSchema[] $columns
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildColumnsCreation(array $columns):void
    {
        foreach ($columns as $column) {
            $tableName = $this->model->getTableAlias();
            $this->migration->addUpCode($this->recordBuilder->addColumn($tableName, $column))
                            ->addDownCode($this->recordBuilder->dropColumn($tableName, $column->name));
        }
    }

    /**
     * @param array|ColumnSchema[] $columns
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildColumnsDrop(array $columns):void
    {
        foreach ($columns as $column) {
            $tableName = $this->model->getTableAlias();
            if ($column->isPrimaryKey && !$column->autoIncrement) {
                $pkName = 'pk_' . $this->model->tableName . '_' . $column->name;
                $this->migration->addDownCode($this->recordBuilder->addPrimaryKey($tableName, [$column->name], $pkName))
                                ->addUpCode($this->recordBuilder->dropPrimaryKey($tableName, [$column->name], $pkName));
            }
            $this->migration->addDownCode($this->recordBuilder->addDbColumn($tableName, $column))
                            ->addUpCode($this->recordBuilder->dropColumn($tableName, $column->name));
        }
    }

    abstract protected function buildColumnChanges(ColumnSchema $current, ColumnSchema $desired, array $changed):void;

    abstract protected function compareColumns(ColumnSchema $current, ColumnSchema $desired):array;

    abstract protected function createEnumMigrations():void;

    abstract protected function isDbDefaultSize(ColumnSchema $current):bool;

    abstract public static function getColumnSchemaBuilderClass(): string;

    /**
     * @return array|\cebe\yii2openapi\lib\items\DbIndex[]
     */
    abstract protected function findTableIndexes():array;

    protected function buildIndexChanges():void
    {
        $haveIndexes = $this->findTableIndexes();
        $wantIndexes = $this->model->indexes;
        $wantIndexNames = array_column($wantIndexes, 'name');
        $haveIndexNames = array_column($haveIndexes, 'name');
        $tableName = $this->model->getTableAlias();
        /**@var \cebe\yii2openapi\lib\items\DbIndex[] $forDrop */
        $forDrop = array_map(
            function ($idx) use ($haveIndexes) {
                return $haveIndexes[$idx];
            },
            array_diff($haveIndexNames, $wantIndexNames)
        );
        /**@var \cebe\yii2openapi\lib\items\DbIndex[] $forCreate */
        $forCreate = array_map(
            function ($idx) use ($wantIndexes) {
                return $wantIndexes[$idx];
            },
            array_diff($wantIndexNames, $haveIndexNames)
        );
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
            $this->migration->addUpCode($this->recordBuilder->dropIndex($tableName, $index->name))
                            ->addDownCode($downCode);
        }
        foreach ($forCreate as $index) {
            $upCode = $index->isUnique
                ? $this->recordBuilder->addUniqueIndex($tableName, $index->name, $index->columns)
                : $this->recordBuilder->addIndex($tableName, $index->name, $index->columns, $index->type);
            $this->migration->addDownCode($this->recordBuilder->dropIndex($tableName, $index->name))
                            ->addUpCode($upCode);
        }
    }

    protected function buildRelations():void
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

    protected function buildRelationsForJunction(ManyToManyRelation $relation):void
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

    protected function foreignKeyName(string $table, string $column, string $foreignTable, string $foreignColumn):string
    {
        $table = $this->normalizeTableName($table);
        $foreignTable = $this->normalizeTableName($foreignTable);
        return substr("fk_{$table}_{$column}_{$foreignTable}_$foreignColumn", 0, 63);
    }

    protected function normalizeTableName(string $tableName):string
    {
        if (preg_match('~^{{%?(.*)}}$~', $tableName, $m)) {
            return $m[1];
        }
        return $tableName;
    }

    protected function unPrefixTableName(string $tableName):string
    {
        return str_replace($this->db->tablePrefix, '', $tableName);
    }

    protected function isNeedUsingExpression(string $fromType, string $toType):bool
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
        return !(in_array($fromType, $dates) && in_array($toType, $dates));
    }

    public function tmpSaveNewCol(\cebe\yii2openapi\db\ColumnSchema $columnSchema)//: ColumnSchema TODO
    {
        $tableName = 'tmp_table_';

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS '.$tableName)->execute();

        if ($columnSchema->name === 'json1') {
            // VarDumper::dump($columnSchema);
            // VarDumper::dump($this->newColStr($columnSchema));
            // die;
        }

        // echo PHP_EOL;
        // VarDumper::dump($columnSchema->xDbType);echo PHP_EOL;


        Yii::$app->db->createCommand()->createTable($tableName, [
            $columnSchema->name => $this->newColStr($columnSchema), // TODO
            // $columnSchema->name.' '.$this->newColStr($columnSchema), // TODO for xDbType
        ])->execute();


        $table = Yii::$app->db->getTableSchema($tableName);

        Yii::$app->db->createCommand()->dropTable($tableName)->execute();

        return $table->columns[$columnSchema->name];
    }

    public function newColStr(\cebe\yii2openapi\db\ColumnSchema $columnSchema): string
    {
        $ctc = new ColumnToCode(\Yii::$app->db->schema, $columnSchema, false, false, true);
        // VarDumper::dump($ctc->getCode()); die;
        // if ($columnSchema->name === 'email') {
        //     VarDumper::dump(ColumnToCode::undoEscapeQuotes($ctc->getCode()));
        // }
        return ColumnToCode::undoEscapeQuotes($ctc->getCode());

        // $builderClass = static::getColumnSchemaBuilderClass();
        // $mysqlCsb = new $builderClass($columnSchema->dbType, $columnSchema->size); // TODO rename var $mysqlCsb
        // if ($columnSchema->allowNull) {
        //     $mysqlCsb->null();
        // } else {
        //     $mysqlCsb->notNull();
        // }

        // if ($columnSchema->defaultValue !== null) {
        //     $mysqlCsb->defaultValue($columnSchema->defaultValue);
        // }

        // if ($columnSchema->unsigned) {
        //     $mysqlCsb->unsigned();
        // }
        // // TODO enum values

        // return $mysqlCsb->__toString();
    }
}
