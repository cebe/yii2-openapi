<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use cebe\yii2openapi\lib\ValidationRulesBuilder;
use yii\base\BaseObject;
use yii\db\ColumnSchema;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use function array_filter;
use function array_map;
use function str_replace;
use const PHP_EOL;

/**
 * @property-read string                                                $tableAlias
 * @property-read array                                                 $uniqueColumnsList
 * @property-read array[]|array                                         $attributesByType
 * @property-read array|\cebe\yii2openapi\lib\items\AttributeRelation[] $hasOneRelations
 */
class DbModel extends BaseObject
{
    /**
     * @var string primary key attribute name
     */
    public $pkName;
    /**
     * @var string model name.
     */
    public $name;

    /**
     * @var string table name. (without brackets and db prefix)
     */
    public $tableName;

    /**
     * @var string description from the schema.
     */
    public $description;

    /**
     * @var array|\cebe\yii2openapi\lib\items\Attribute[] model attributes.
     */
    public $attributes = [];

    /**
     * @var array|\cebe\yii2openapi\lib\items\AttributeRelation[] database relations.
     */
    public $relations = [];

    /**
     * @var array|\cebe\yii2openapi\lib\items\ManyToManyRelation[] many to many relations.
     */
    public $many2many = [];

    public $junctionCols = [];

    /**
     * @var \cebe\yii2openapi\lib\items\DbIndex[]|array
     */
    public $indexes = [];

    public $isNotDb = false;

    public function getTableAlias(): string
    {
        return '{{%' . $this->tableName . '}}';
    }

    public function getClassName()
    {
        return Inflector::id2camel($this->name, '_');
    }

    public function getValidationRules(): string
    {
        $rules = (new ValidationRulesBuilder($this))->build();
        $rules = array_map(function ($rule) {
            return (string)$rule;
        }, $rules);
        $rules = VarDumper::export($rules);
        return str_replace([PHP_EOL, "\'", "'[[", "]',"], [PHP_EOL . '        ', "'", '[[', '],'], $rules);
    }

    /**
     * @return \cebe\yii2openapi\lib\items\AttributeRelation[]|array
     */
    public function getHasOneRelations(): array
    {
        return array_filter(
            $this->relations,
            static function (AttributeRelation $relation) {
                return $relation->isHasOne();
            }
        );
    }

    public function getPkAttribute(): Attribute
    {
        return $this->attributes[$this->pkName];
    }

    /**
     * @return ColumnSchema[]
     */
    public function attributesToColumnSchema(): array
    {
        return $this->isNotDb? []: array_reduce(
            $this->attributes,
            static function ($acc, Attribute $attribute) {
                if (!$attribute->isVirtual) {
                    $acc[$attribute->columnName] = $attribute->toColumnSchema();
                }
                return $acc;
            },
            []
        );
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function getEnumAttributes(): array
    {
        return array_filter(
            $this->attributes,
            function (Attribute $attribute) {
                return !$attribute->isVirtual && StringHelper::startsWith($attribute->dbType, 'enum')
                    && !empty($attribute->enumValues);
            }
        );
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function virtualAttributes(): array
    {
        return array_filter($this->attributes, function (Attribute $attribute) {
            return $attribute->isVirtual;
        });
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function dbAttributes(): array
    {
        return array_filter($this->attributes, function (Attribute $attribute) {
            return !$attribute->isVirtual;
        });
    }
}
