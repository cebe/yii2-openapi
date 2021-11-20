<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use Yii;
use yii\base\BaseObject;
use yii\db\ColumnSchema;
use yii\db\Schema;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use function implode;
use function sort;
use function strtolower;

/**
 * @property-read string                 $viaTableName
 * @property-read string[]               $link
 * @property-read string                 $relatedFk
 * @property-read string                 $selfFk
 * @property-read string                 $viaTableAlias
 * @property-read string                 $camelName
 * @property-read string                 $className
 * @property-read string                 $relatedClassName
 * @property-read \yii\db\ColumnSchema[] $columnSchema
 * @property-read string[]               $viaLink
 */
class ManyToManyRelation extends BaseObject
{
    /**@var string */
    public $name;

    /**@var string */
    public $schemaName;

    /**@var string */
    public $relatedSchemaName;

    /**@var string */
    public $tableName;

    /**@var string */
    public $relatedTableName;

    /**@var bool* */
    public $hasViaModel = false;

    /**@var \cebe\yii2openapi\lib\items\Attribute */
    public $pkAttribute;

    /**@var \cebe\yii2openapi\lib\items\Attribute */
    public $relatedPkAttribute;

    /**@var string* */
    public $viaModelName;

    /**@var string* */
    public $viaRelationName;

    /**@var string* */
    public $fkProperty;

    /**@var string* */
    public $relatedFkProperty;

    public function getCamelName():string
    {
        return Inflector::camelize($this->name);
    }

    public function getClassName():string
    {
        return Inflector::id2camel($this->schemaName, '_');
    }

    public function getRelatedClassName():string
    {
        return Inflector::id2camel($this->relatedSchemaName, '_');
    }

    /**
     * Resolve junction model name. If model schema with this name not defined, viaTable will be used
     * @example
     *  model Document n-n Label -> Documents2Labels
     *  model Post  n-n Tag  -> Posts2Tags
     */
    public function getViaModelName():string
    {
        if (!$this->viaModelName) {
            $names = [
                Inflector::pluralize(Inflector::id2camel($this->className, '_')),
                Inflector::pluralize(Inflector::id2camel($this->relatedClassName, '_')),
            ];
            sort($names);
            return implode('2', $names);
        }
        return $this->viaModelName;
    }

    /**
     * For cases when relation name and viaModel are different
     * @return string
     */
    public function getViaRelationName():string
    {
        return $this->viaRelationName ?: $this->getViaModelName();
    }

    /**
     * Generate junction table name
     * @example
     *  model Document n-n Label -> documents2labels
     *  model Post  n-n Tag  -> posts2tags
     */
    public function getViaTableName():string
    {
        $names = [
            strtolower(Inflector::camel2id(Inflector::pluralize($this->schemaName), '_')),
            strtolower(Inflector::camel2id(Inflector::pluralize($this->relatedSchemaName), '_')),
        ];
        sort($names);
        return implode('2', $names);
    }

    public function getViaTableAlias():string
    {
        return '{{%' . $this->getViaTableName() . '}}';
    }

    public function getSelfFk():string
    {
        $fk = $this->fkProperty ?? $this->schemaName;
        return strtolower(Inflector::camel2id($fk, '_')) . '_id';
    }

    public function getRelatedFk():string
    {
        $fk = $this->relatedFkProperty ?? $this->relatedSchemaName;
        return strtolower(Inflector::camel2id($fk, '_')) . '_id';
    }

    public function getLink():array
    {
        return [$this->pkAttribute->propertyName => $this->getRelatedFk()];
    }

    public function linkToString(array $link):string
    {
        return str_replace(
            [',', '=>', ', ]'],
            [', ', ' => ', ']'],
            preg_replace('~\s+~', '', VarDumper::export($link))
        );
    }

    public function getViaLink():array
    {
        return [$this->getSelfFk() => $this->pkAttribute->propertyName];
    }

    /**
     * Default columns for generate migration for junction table, when viaModel not defined
     * @return array|\yii\db\ColumnSchema[]
     */
    public function getColumnSchema():array
    {
        $pkTypeMap = [
            Schema::TYPE_PK => Schema::TYPE_INTEGER,
            Schema::TYPE_UPK => Schema::TYPE_INTEGER,
            Schema::TYPE_BIGPK => Schema::TYPE_BIGINT,
            Schema::TYPE_UBIGPK => Schema::TYPE_BIGINT,
        ];
        $castPkColumn = static function (ColumnSchema $col) use ($pkTypeMap) {
            $col->allowNull = false;
            if (isset($pkTypeMap[$col->type])) {
                $col->type = $pkTypeMap[$col->type];
            }
            if (isset($pkTypeMap[$col->dbType])) {
                $col->dbType = $pkTypeMap[$col->dbType];
            }
            return $col;
        };
        $selfPkColumn = $castPkColumn($this->pkAttribute->toColumnSchema());
        $selfPkColumn->name = $this->selfFk;
        $relatedPkColumn = $castPkColumn($this->relatedPkAttribute->toColumnSchema());
        $relatedPkColumn->name = $this->relatedFk;
        return [
            $this->selfFk => $selfPkColumn,
            $this->relatedFk => $relatedPkColumn,
        ];
    }

    /**
     * Relations for migration for junction table, when viaModel not defined
     * @return array|\cebe\yii2openapi\lib\items\AttributeRelation
     */
    public function getRelations():array
    {
        return [
            Yii::createObject(AttributeRelation::class, [$this->selfFk, $this->tableName, $this->className])
               ->asHasOne(['id' => $this->selfFk]),
            Yii::createObject(
                AttributeRelation::class,
                [$this->relatedFk, $this->relatedTableName, $this->relatedClassName]
            )
               ->asHasOne(['id' => $this->relatedFk]),
        ];
    }
}
