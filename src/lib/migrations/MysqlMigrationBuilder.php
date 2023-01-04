<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\migrations;

use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\ColumnToCode;
use cebe\yii2openapi\lib\items\DbIndex;
use yii\base\NotSupportedException;
use yii\db\ColumnSchema;
use yii\db\IndexConstraint;
use yii\db\Schema;
use \Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

final class MysqlMigrationBuilder extends BaseMigrationBuilder
{
    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function buildColumnChanges(ColumnSchema $current, ColumnSchema $desired, array $changed):void
    {
        $newColumn = clone $current;
        foreach ($changed as $attr) {
            $newColumn->$attr = $desired->$attr;
        }
        if (static::isEnum($newColumn)) {
            $newColumn->dbType = 'enum'; // TODO this is concretely not correct
        }
        $this->migration->addUpCode($this->recordBuilder->alterColumn($this->model->getTableAlias(), $newColumn))
                        ->addDownCode($this->recordBuilder->alterColumn($this->model->getTableAlias(), $current));
    }

    protected function compareColumns(ColumnSchema $current, ColumnSchema $desired):array
    {
        $changedAttributes = [];
        $tableAlias = $this->model->getTableAlias();

        $this->modifyCurrent($current);
        $this->modifyDesired($desired);
        $this->modifyDesiredInContextOfCurrent($current, $desired);

        // Why this is needed? Often manually created ColumnSchem instance have dbType 'varchar' with size 255 and ColumnSchema fetched from db have 'varchar(255)'. So varchar !== varchar(255). such normal mistake was leading to errors. So desired column is saved in temporary table and it is fetched from that temp. table and then compared with current ColumnSchema
        $desiredFromDb = $this->tmpSaveNewCol($tableAlias, $desired);
        $this->modifyDesired($desiredFromDb);
        $this->modifyDesiredInContextOfCurrent($current, $desiredFromDb);

        foreach (['type', 'size', 'allowNull', 'defaultValue', 'enumValues'
                    , 'dbType', 'phpType'
                    , 'precision', 'scale', 'unsigned'
        ] as $attr) {
            if ($attr === 'defaultValue') {
                if ($this->isDefaultValueChanged($current, $desiredFromDb)) {
                    $changedAttributes[] = $attr;
                }
            } else {
                if ($current->$attr !== $desiredFromDb->$attr) {
                    $changedAttributes[] = $attr;
                }
            }
        }
        return $changedAttributes;
    }

    protected function createEnumMigrations():void
    {
        // execute via default
    }

    protected function isDbDefaultSize(ColumnSchema $current):bool
    {
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
        return isset($defaults[$current->type]);
    }

    /**
     * @return array|DbIndex[]
     */
    protected function findTableIndexes():array
    {
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

    public static function getColumnSchemaBuilderClass(): string
    {
        if (ApiGenerator::isMysql()) {
            return \yii\db\mysql\ColumnSchemaBuilder::class;
        } elseif (ApiGenerator::isMariaDb()) {
            return \SamIT\Yii2\MariaDb\ColumnSchemaBuilder::class;
        }
    }

    public function modifyCurrent(ColumnSchema $current): void
    {
        /** @var $current \yii\db\mysql\ColumnSchema */
        if ($current->phpType === 'integer' && $current->defaultValue !== null) {
            $current->defaultValue = (int)$current->defaultValue;
        }
    }

    public function modifyDesired(ColumnSchema $desired): void
    {
        /** @var $desired cebe\yii2openapi\db\ColumnSchema|\yii\db\mysql\ColumnSchema */
        if ($desired->phpType === 'int' && $desired->defaultValue !== null) {
            $desired->defaultValue = (int)$desired->defaultValue;
        }

        if ($decimalAttributes = ColumnToCode::isDecimalByDbType($desired->dbType)) {
            $desired->precision = $decimalAttributes['precision'];
            $desired->scale = $decimalAttributes['scale'];
        }
    }

    public function modifyDesiredInContextOfCurrent(ColumnSchema $current, ColumnSchema $desired): void
    {
        /** @var $current \yii\db\mysql\ColumnSchema */
        /** @var $desired cebe\yii2openapi\db\ColumnSchema|\yii\db\mysql\ColumnSchema */
        if ($current->dbType === 'tinyint(1)' && $desired->type === 'boolean') {
            if (is_bool($desired->defaultValue) || is_string($desired->defaultValue)) {
                $desired->defaultValue = (int)$desired->defaultValue;
            }
        }

        if ($current->type === $desired->type && !$desired->size && $this->isDbDefaultSize($current)) {
            $desired->size = $current->size;
        }
    }
}
