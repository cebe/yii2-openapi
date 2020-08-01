<?php

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;
use yii\db\ColumnSchema;
use yii\helpers\StringHelper;
use function array_filter;
use function in_array;

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

    public function getAttributesByType():array
    {
        //Todo: may be more smarter validator resolver, include name patterns
        $byType =
            ['safe' => [], 'required' => [], 'int' => [], 'bool' => [], 'float' => [], 'string' => [], 'ref' => []];
        foreach ($this->attributes as $attribute) {
            if ($attribute->isReadOnly()) {
                continue;
            }
            if ($attribute->isRequired()) {
                $byType['required'][$attribute->columnName] = $attribute->columnName;
            }

            if ($attribute->isReference()) {
                if (in_array($attribute->phpType, ['int', 'string'])) {
                    $byType[$attribute->phpType][$attribute->columnName] = $attribute->columnName;
                }
                $byType['ref'][] = ['attr' => $attribute->columnName, 'rel' => $attribute->camelName()];
                continue;
            }

            if (in_array($attribute->phpType, ['int', 'string', 'bool', 'float'])) {
                $byType[$attribute->phpType][$attribute->columnName] = $attribute->columnName;
                continue;
            }

            if ($attribute->phpType === 'double') {
                $byType['float'][$attribute->columnName] = $attribute->columnName;
                continue;
            }

            $byType['safe'][$attribute->columnName] = $attribute->columnName;
        }
        return $byType;
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
        return array_filter($this->relations,
            static function(AttributeRelation $relation) {
                return $relation->isHasOne();
            });
    }
    /**
     * @return ColumnSchema[]
     */
    public function attributesToColumnSchema():array
    {
        return array_reduce($this->attributes,
            static function($acc, Attribute $attribute) {
                $acc[$attribute->columnName] = $attribute->toColumnSchema();
                return $acc;
            },
            []);
    }
    /**
     * @return array|\cebe\yii2openapi\lib\items\Attribute[]
     */
    public function getEnumAttributes():array
    {
        return array_filter($this->attributes,
            function(Attribute $attribute) {
                return StringHelper::startsWith($attribute->dbType, 'enum') && !empty($attribute->enumValues);
            });
    }
}