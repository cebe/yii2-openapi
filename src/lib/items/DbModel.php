<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use cebe\yii2openapi\lib\ValidationRulesBuilder;
use yii\base\BaseObject;
use yii\db\ColumnSchema;
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

    public function getTableAlias():string
    {
        return '{{%' . $this->tableName . '}}';
    }

    public function getValidationRules():string
    {
        $rules = (new ValidationRulesBuilder($this))->build();
        $rules = array_map(function ($rule) {
            return (string) $rule;
        }, $rules);
        $rules = VarDumper::export($rules);
        return str_replace([PHP_EOL, "\'", "'[[", "]',"], [PHP_EOL.'        ', "'", '[[', '],'], $rules);
    }

    public function getUniqueColumnsList():array
    {
        $uniques = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->isUnique()) {
                $uniques[] = $attribute->columnName;
            }
        }
        return $uniques;
    }

    /**
     * @return \cebe\yii2openapi\lib\items\AttributeRelation[]|array
     */
    public function getHasOneRelations():array
    {
        return array_filter(
            $this->relations,
            static function (AttributeRelation $relation) {
                return $relation->isHasOne();
            }
        );
    }

    /**
     * @return ColumnSchema[]
     */
    public function attributesToColumnSchema():array
    {
        return array_reduce(
            $this->attributes,
            static function ($acc, Attribute $attribute) {
                $acc[$attribute->columnName] = $attribute->toColumnSchema();
                return $acc;
            },
            []
        );
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function getEnumAttributes():array
    {
        return array_filter(
            $this->attributes,
            function (Attribute $attribute) {
                return StringHelper::startsWith($attribute->dbType, 'enum') && !empty($attribute->enumValues);
            }
        );
    }
}
