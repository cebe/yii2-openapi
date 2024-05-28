<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\CustomSpecAttr;
use cebe\yii2openapi\lib\exceptions\InvalidDefinitionException;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\AttributeRelation;
use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use cebe\yii2openapi\lib\items\ManyToManyRelation;
use cebe\yii2openapi\lib\items\NonDbRelation;
use cebe\yii2openapi\lib\openapi\ComponentSchema;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use function explode;
use function strpos;
use function strtolower;

class AttributeResolver
{
    /**
     * @var Attribute[]|array
     */
    private $attributes = [];

    /**
     * @var AttributeRelation[]|array
     */
    private $relations = [];
    /**
     * @var NonDbRelation[]|array
     */
    private $nonDbRelations = [];
    /**
     * @var ManyToManyRelation[]|array
     */
    private $many2many = [];

    /**
     * @var string
     */
    private $schemaName;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var ComponentSchema
     */
    private $schema;

    /**
     * @var \cebe\yii2openapi\lib\items\JunctionSchemas
     */
    private $junctions;

    /** @var bool */
    private $isJunctionSchema;

    /** @var bool */
    private $hasMany2Many;

    /** @var Config */
    private $config;

    public function __construct(string $schemaName, ComponentSchema $schema, JunctionSchemas $junctions, ?Config $config = null)
    {
        $this->schemaName = $schemaName;
        $this->schema = $schema;
        $this->tableName = $schema->resolveTableName($schemaName);
        $this->junctions = $junctions;
        $this->isJunctionSchema = $junctions->isJunctionSchema($schemaName);
        $this->hasMany2Many = $junctions->hasMany2Many($schemaName);
        $this->config = $config;
    }

    /**
     * @return \cebe\yii2openapi\lib\items\DbModel
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    public function resolve():DbModel
    {
        foreach ($this->schema->getProperties() as $property) {
            /** @var $property \cebe\yii2openapi\lib\openapi\PropertySchema */

            $isRequired = $this->schema->isRequiredProperty($property->getName());
            $nullableValue = $property->getProperty()->getSerializableData()->nullable ?? null;
            if ($nullableValue === false) { // see docs in README regarding NOT NULL, required and nullable
                $isRequired = true;
            }

            if ($this->isJunctionSchema) {
                $this->resolveJunctionTableProperty($property, $isRequired);
            } elseif ($this->hasMany2Many) {
                $this->resolveHasMany2ManyTableProperty($property, $isRequired);
            } else {
                $this->resolveProperty($property, $isRequired, $nullableValue);
            }
        }
        return Yii::createObject(DbModel::class, [
            [
                'pkName' => $this->schema->getPkName(),
                'name' => $this->schemaName,
                'tableName' => $this->tableName,
                'description' => $this->schema->getDescription(),
                'attributes' => $this->attributes,
                'relations' => $this->relations,
                'nonDbRelations' => $this->nonDbRelations,
                'many2many' => $this->many2many,
                'indexes' => $this->prepareIndexes($this->schema->getIndexes()),
                //For valid primary keys for junction tables
                'junctionCols' => $this->isJunctionSchema ? $this->junctions->junctionCols($this->schemaName) : [],
                'isNotDb' => $this->schema->isNonDb(),
            ],
        ]);
    }

    /**
     * @param \cebe\yii2openapi\lib\openapi\PropertySchema $property
     * @param bool                                         $isRequired
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolveJunctionTableProperty(PropertySchema $property, bool $isRequired):void
    {
        if ($this->junctions->isJunctionProperty($this->schemaName, $property->getName())) {
            $junkAttribute = $this->junctions->byJunctionSchema($this->schemaName)[$property->getName()];
            $attribute = Yii::createObject(Attribute::class, [$property->getName()]);
            $attribute->setRequired($isRequired)
                      ->setDescription($property->getAttr('description', ''))
                      ->setReadOnly($property->isReadonly())
                      ->setIsPrimary($property->isPrimaryKey())
                      ->asReference($junkAttribute['relatedClassName'])
                      ->setPhpType($junkAttribute['phpType'])
                      ->setDbType($junkAttribute['dbType'])
                      ->setForeignKeyColumnName($property->fkColName);
            $relation = Yii::createObject(AttributeRelation::class, [
                $property->getName(),
                $junkAttribute['relatedTableName'],
                $junkAttribute['relatedClassName'],
            ])->asHasOne([$junkAttribute['foreignPk'] => $attribute->columnName]);
            $this->relations[$property->getName()] = $relation;
            $this->attributes[$property->getName()] =
                $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
        } else {
            $this->resolveProperty($property, $isRequired);
        }
    }

    /**
     * @param \cebe\yii2openapi\lib\openapi\PropertySchema $property
     * @param bool                                         $isRequired
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolveHasMany2ManyTableProperty(PropertySchema $property, bool $isRequired):void
    {
        if ($this->junctions->isManyToManyProperty($this->schemaName, $property->getName())) {
            return;
        }
        if ($this->junctions->isJunctionRef($this->schemaName, $property->getName())) {
            $junkAttribute = $this->junctions->indexByJunctionRef()[$property->getName()][$this->schemaName];
            $junkRef = $property->getName();
            $junkProperty = $junkAttribute['property'];
            $viaModel = $this->junctions->trimPrefix($junkAttribute['junctionSchema']);

            $relation = Yii::createObject(ManyToManyRelation::class, [
                [
                    'name' => Inflector::pluralize($junkProperty),
                    'schemaName' => $this->schemaName,
                    'relatedSchemaName' => $junkAttribute['relatedClassName'],
                    'tableName' => $this->tableName,
                    'relatedTableName' => $junkAttribute['relatedTableName'],
                    'pkAttribute' => $this->attributes[$this->schema->getPkName()],
                    'hasViaModel' => true,
                    'viaModelName' => $viaModel,
                    'viaRelationName' => Inflector::id2camel($junkRef, '_'),
                    'fkProperty' => $junkAttribute['pairProperty'],
                    'relatedFkProperty' => $junkAttribute['property'],
                ],
            ]);
            $this->many2many[Inflector::pluralize($junkProperty)] = $relation;

            $this->relations[Inflector::pluralize($junkRef)] =
                Yii::createObject(AttributeRelation::class, [$junkRef, $junkAttribute['junctionTable'], $viaModel])
                   ->asHasMany([$junkAttribute['pairProperty'] . '_id' => $this->schema->getPkName()]);
            return;
        }

        $this->resolveProperty($property, $isRequired);
    }

    /**
     * @param \cebe\yii2openapi\lib\openapi\PropertySchema $property
     * @param bool                                         $isRequired
     * @param bool|null|string                             $nullableValue if string then its value will be only constant `ARG_ABSENT`. Default `null` is avoided because it can be in passed value in method call
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolveProperty(
        PropertySchema $property,
        bool $isRequired,
        $nullableValue = 'ARG_ABSENT'
    ):void {
        if ($nullableValue === 'ARG_ABSENT') {
            $nullableValue = $property->getProperty()->getSerializableData()->nullable ?? null;
        }
        $attribute = Yii::createObject(Attribute::class, [$property->getName()]);
        $attribute->setRequired($isRequired)
                  ->setDescription($property->getAttr('description', ''))
                  ->setReadOnly($property->isReadonly())
                  ->setDefault($property->guessDefault())
                  ->setXDbType($property->getAttr(CustomSpecAttr::DB_TYPE))
                  ->setXDbDefaultExpression($property->getAttr(CustomSpecAttr::DB_DEFAULT_EXPRESSION))
                  ->setNullable($nullableValue)
                  ->setIsPrimary($property->isPrimaryKey())
                  ->setForeignKeyColumnName($property->fkColName);
        if ($property->isReference()) {
            if ($property->isVirtual()) {
                throw new InvalidDefinitionException('References not supported for virtual attributes');
            }
            
            if ($property->isNonDbReference()) {
                $attribute->asNonDbReference($property->getRefClassName());
                $relation = Yii::createObject(
                    NonDbRelation::class,
                    [$property->getName(), $property->getRefClassName(), NonDbRelation::HAS_ONE]
                );

                $this->nonDbRelations[$property->getName()] = $relation;
                return;
            }

            $fkProperty = $property->getTargetProperty();
            if (!$fkProperty && !$property->getRefSchema()->isObjectSchema()) {
                $this->resolvePropertyRef($property, $attribute);
                return;
            }
            if (!$fkProperty) {
                return;
            }
            $relatedClassName = $property->getRefClassName();
            $relatedTableName = $property->getRefSchema()->resolveTableName($relatedClassName);
            [$min, $max] = $fkProperty->guessMinMax();
            $attribute->asReference($relatedClassName);
            $attribute->setPhpType($fkProperty->guessPhpType())
                      ->setDbType($fkProperty->guessDbType(true))
                      ->setSize($fkProperty->getMaxLength())
                      ->setDescription($property->getRefSchema()->getDescription())
                      ->setDefault($fkProperty->guessDefault())
                      ->setLimits($min, $max, $fkProperty->getMinLength());

            $relation = Yii::createObject(
                AttributeRelation::class,
                [$property->getName(), $relatedTableName, $relatedClassName]
            )
                           ->asHasOne([$fkProperty->getName() => $attribute->columnName]);
            $relation->onUpdateFkConstraint = $property->onUpdateFkConstraint;
            $relation->onDeleteFkConstraint = $property->onDeleteFkConstraint;
            if ($property->isRefPointerToSelf()) {
                $relation->asSelfReference();
            }
            $this->relations[$property->getName()] = $relation;
        }
        if (!$property->isReference() && !$property->hasRefItems()) {
            [$min, $max] = $property->guessMinMax();
            $attribute->setIsVirtual($property->isVirtual())
                      ->setPhpType($property->guessPhpType())
                      ->setDbType($property->guessDbType())
                      ->setSize($property->getMaxLength())
                      ->setLimits($min, $max, $property->getMinLength());
            if ($property->hasEnum()) {
                $attribute->setEnumValues($property->getAttr('enum'));
            }
        }

        if ($property->hasRefItems()) {
            if ($property->isVirtual()) {
                throw new InvalidDefinitionException('References not supported for virtual attributes');
            }

            if ($property->isNonDbReference()) {
                $attribute->asNonDbReference($property->getRefClassName());
                $relation = Yii::createObject(
                    NonDbRelation::class,
                    [$property->getName(), $property->getRefClassName(), NonDbRelation::HAS_MANY]
                );

                $this->nonDbRelations[$property->getName()] = $relation;
                return;
            }

            if ($property->isRefPointerToSelf()) {
                $relatedClassName = $property->getRefClassName();
                $attribute->setPhpType($relatedClassName . '[]');
                $relatedTableName = $this->tableName;
                $fkProperty = $property->getSelfTargetProperty();
                if ($fkProperty && !$fkProperty->isReference()
                    && !StringHelper::endsWith(
                        $fkProperty->getName(),
                        '_id'
                    )) {
                    $this->relations[$property->getName()] =
                        Yii::createObject(
                            AttributeRelation::class,
                            [$property->getName(), $relatedTableName, $relatedClassName]
                        )
                           ->asHasMany([$fkProperty->getName() => $fkProperty->getName()])->asSelfReference();
                    return;
                }
                $foreignPk = Inflector::camel2id($fkProperty->getName(), '_') . '_id';
                $this->relations[$property->getName()] =
                    Yii::createObject(
                        AttributeRelation::class,
                        [$property->getName(), $relatedTableName, $relatedClassName]
                    )
                       ->asHasMany([$foreignPk => $this->schema->getPkName()]);
                return;
            }
            $relatedClassName = $property->getRefClassName();
            $relatedTableName = $property->getRefSchema()->resolveTableName($relatedClassName);
            if ($this->catchManyToMany(
                $property->getName(),
                $relatedClassName,
                $relatedTableName,
                $property->getRefSchema()
            )) {
                return;
            }
            $attribute->setPhpType($relatedClassName . '[]');
            $this->relations[$property->getName()] =
                Yii::createObject(
                    AttributeRelation::class,
                    [$property->getName(), $relatedTableName, $relatedClassName]
                )
                   ->asHasMany([Inflector::camel2id($this->schemaName, '_') . '_id' => $this->schema->getPkName()]);
            return;
        }
        if ($this->schema->isNonDb() && $attribute->isReference()) {
            $this->attributes[$property->getName()] = $attribute;
            return;
        }
        $this->attributes[$property->getName()] =
            $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
    }

    /**
     * Check and register many-to-many relation
     * - property name for many-to-many relation should be equal lower-cased, pluralized schema name
     * - referenced schema should contain mirrored reference to current schema
     * @param string $propertyName
     * @param string $relatedSchemaName
     * @param string $relatedTableName
     * @param ComponentSchema $refSchema
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    protected function catchManyToMany(
        string $propertyName,
        string $relatedSchemaName,
        string $relatedTableName,
        ComponentSchema $refSchema
    ):bool {
        if (strtolower(Inflector::id2camel($propertyName, '_'))
            !== strtolower(Inflector::pluralize($relatedSchemaName))) {
            return false;
        }
        $expectedPropertyName = strtolower(Inflector::pluralize(Inflector::camel2id($this->schemaName, '_')));
        if (!$refSchema->hasProperty($expectedPropertyName)) {
            return false;
        }
        $refProperty = $refSchema->getProperty($expectedPropertyName);
        if (!$refProperty) {
            return false;
        }
        $refClassName = $refProperty->hasRefItems() ? $refProperty->getRefSchemaName() : null;
        if ($refClassName !== $this->schemaName) {
            return false;
        }
        $relation = Yii::createObject(ManyToManyRelation::class, [
            [
                'name' => $propertyName,
                'schemaName' => $this->schemaName,
                'relatedSchemaName' => $relatedSchemaName,
                'tableName' => $this->tableName,
                'relatedTableName' => $relatedTableName,
                'pkAttribute' => $this->attributes[$this->schema->getPkName()],
            ],
        ]);
        $this->many2many[$propertyName] = $relation;
        return true;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    protected function guessFakerStub(Attribute $attribute, PropertySchema $property):?string
    {
        $resolver = Yii::createObject(['class' => FakerStubResolver::class], [$attribute, $property, $this->config]);
        return $resolver->resolve();
    }

    /**
     * @param array $indexes
     * @return array|DbIndex[]
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     */
    protected function prepareIndexes(array $indexes):array
    {
        $dbIndexes = [];
        foreach ($indexes as $index) {
            $unique = false;
            if (strpos($index, ':') !== false) {
                [$indexType, $props] = explode(':', $index);
            } else {
                $props = $index;
                $indexType = null;
            }
            if ($indexType === 'unique') {
                $indexType = null;
                $unique = true;
            }
            $props = array_map('trim', explode(',', trim($props)));
            $columns = [];
            $xFkColumnNames = [];
            foreach ($this->attributes as $key => $value) {
                if (!empty($value->fkColName)) {
                    $xFkColumnNames[$value->fkColName] = $key;
                }
            }
            foreach ($props as $prop) {
                // for more info see test tests/specs/fk_col_name/fk_col_name.yaml
                // File: ForeignKeyColumnNameTest::testIndexForColumnWithCustomName
                // first check direct column names
                if (!isset($this->attributes[$prop])) {
                    // then check x-fk-column-name
                    if (!in_array($prop, array_keys($xFkColumnNames))) {
                        // then check relations/reference e.g. `user`/`user_id`
                        $refPropName = (substr($prop, -3) === '_id') ? rtrim($prop, '_id') : null;
                        if ($refPropName && !isset($this->attributes[$refPropName])) {
                            throw new InvalidDefinitionException('Invalid index definition - property ' . $prop
                                . ' not declared');
                        } else {
                            $prop = $refPropName;
                        }
                    } else {
                        $prop = $xFkColumnNames[$prop];
                    }
                }
                $columns[] = $this->attributes[$prop]->columnName;
            }
            $dbIndex = DbIndex::make($this->tableName, $columns, $indexType, $unique);
            $dbIndexes[$dbIndex->name] = $dbIndex;
        }
        return $dbIndexes;
    }

    /**
     * @param \cebe\yii2openapi\lib\openapi\PropertySchema $property
     * @param \cebe\yii2openapi\lib\items\Attribute        $attribute
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolvePropertyRef(PropertySchema $property, Attribute $attribute):void
    {
        $fkProperty = new PropertySchema(
            $property->getRefSchema()->getSchema(),
            $property->getName(),
            $this->schema
        );
        [$min, $max] = $fkProperty->guessMinMax();
        $attribute->setPhpType($fkProperty->guessPhpType())
                  ->setDbType($fkProperty->guessDbType(true))
                  ->setSize($fkProperty->getMaxLength())
                  ->setDescription($fkProperty->getAttr('description'))
                  ->setDefault($fkProperty->guessDefault())
                  ->setLimits($min, $max, $fkProperty->getMinLength());
        $this->attributes[$property->getName()] =
            $attribute->setFakerStub($this->guessFakerStub($attribute, $fkProperty));
    }
}
