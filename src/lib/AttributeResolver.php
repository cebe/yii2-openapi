<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\exceptions\InvalidDefinitionException;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\lib\items\AttributeRelation;
use cebe\yii2openapi\lib\items\DbIndex;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use cebe\yii2openapi\lib\items\ManyToManyRelation;
use Throwable;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use function explode;
use function in_array;
use function is_string;
use function str_replace;
use function strpos;
use function strtolower;
use function substr;

class AttributeResolver
{
    public const REFERENCE_PATH = '/components/schemas/';
    public const REFERENCE_PATH_LEN = 20;

    /**
     * @var Attribute[]|array
     */
    private $attributes = [];

    /**
     * @var AttributeRelation[]|array
     */
    private $relations = [];

    /**
     * @var ManyToManyRelation[]|array
     */
    private $many2many = [];

    /**
     * @var string
     */
    private $schemaName;

    /**
     * @var \cebe\openapi\spec\Schema
     */
    private $componentSchema;

    private $primaryKey;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var \cebe\yii2openapi\lib\items\JunctionSchemas
     */
    private $junctions;

    /**@var bool */
    private $isJunctionSchema;

    /**@var bool */
    private $hasMany2Many;

    public function __construct(string $schemaName, Schema $componentSchema, JunctionSchemas $junctions)
    {
        $this->schemaName = $schemaName;
        $this->componentSchema = $componentSchema;
        $this->primaryKey = $componentSchema->{CustomSpecAttr::PRIMARY_KEY} ?? 'id';
        $this->tableName = $componentSchema->{CustomSpecAttr::TABLE} ?? self::tableNameBySchema($this->schemaName);
        $this->junctions = $junctions;
        $this->isJunctionSchema = $junctions->isJunctionSchema($schemaName);
        $this->hasMany2Many = $junctions->hasMany2Many($schemaName);
    }

    /**
     * @return \cebe\yii2openapi\lib\items\DbModel
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     */
    public function resolve(): DbModel
    {
        $requiredProps = $this->componentSchema->required ?? [];
        foreach ($this->componentSchema->properties as $propertyName => $property) {
            $isRequired = in_array($propertyName, $requiredProps);
            if ($this->isJunctionSchema) {
                $this->resolveJunctionTableProperty($propertyName, $property, $isRequired);
            } elseif ($this->hasMany2Many) {
                $this->resolveHasMany2ManyTableProperty($propertyName, $property, $isRequired);
            } else {
                $this->resolveProperty($propertyName, $property, $isRequired);
            }
        }
        $indexes = $this->componentSchema->{CustomSpecAttr::INDEXES} ?? [];
        return new DbModel([
            'pkName' => $this->primaryKey,
            'name' => $this->schemaName,
            'tableName' => $this->tableName,
            'description' => $this->componentSchema->description,
            'attributes' => $this->attributes,
            'relations' => $this->relations,
            'many2many' => $this->many2many,
            'indexes' => $this->prepareIndexes($indexes),
            //For valid primary keys for junction tables
            'junctionCols' => $this->isJunctionSchema ? $this->junctions->junctionCols($this->schemaName) : []
        ]);
    }

    public static function tableNameBySchema(string $schemaName): string
    {
        return Inflector::camel2id(StringHelper::basename(Inflector::pluralize($schemaName)), '_');
    }

    protected function resolveJunctionTableProperty($propertyName, SpecObjectInterface $property, bool $isRequired)
    {
        if ($this->junctions->isJunctionProperty($this->schemaName, $propertyName)) {
            $junkAttribute = $this->junctions->byJunctionSchema($this->schemaName)[$propertyName];
            $attribute = new Attribute($propertyName);
            $attribute->setRequired($isRequired)
                ->setDescription($property->description ?? '')
                ->setReadOnly($property->readOnly ?? false)
                ->setIsPrimary($propertyName === $this->primaryKey)
                ->asReference($junkAttribute['relatedClassName'])
                ->setPhpType($junkAttribute['phpType'])
                ->setDbType($junkAttribute['dbType']);
            $relation = (new AttributeRelation($propertyName, $junkAttribute['relatedTableName'], $junkAttribute['relatedClassName']))
                ->asHasOne([$junkAttribute['foreignPk'] => $attribute->columnName]);
            $this->relations[$propertyName] = $relation;
            $this->attributes[$propertyName] = $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
        } else {
            $this->resolveProperty($propertyName, $property, $isRequired);
        }
    }

    protected function resolveHasMany2ManyTableProperty($propertyName, SpecObjectInterface $property, bool $isRequired)
    {
        if ($this->junctions->isManyToManyProperty($this->schemaName, $propertyName)) {
            return;
        }
        if ($this->junctions->isJunctionRef($this->schemaName, $propertyName)) {
            $junkAttribute = $this->junctions->indexByJunctionRef()[$propertyName][$this->schemaName];
            $junkRef = $propertyName;
            $junkProperty = $junkAttribute['property'];
            $viaModel = $this->junctions->trimPrefix($junkAttribute['junctionSchema']);

            $relation = new ManyToManyRelation([
                'name' => Inflector::pluralize($junkProperty),
                'schemaName' => $this->schemaName,
                'relatedSchemaName' => $junkAttribute['relatedClassName'],
                'tableName' => $this->tableName,
                'relatedTableName' => $junkAttribute['relatedTableName'],
                'pkAttribute' => $this->attributes[$this->primaryKey],
                'hasViaModel' => true,
                'viaModelName' => $viaModel,
                'viaRelationName' => Inflector::id2camel($junkRef, '_'),
                'fkProperty' => $junkAttribute['pairProperty'],
                'relatedFkProperty' => $junkAttribute['property'],
            ]);
            $this->many2many[Inflector::pluralize($junkProperty)] = $relation;

            $this->relations[Inflector::pluralize($junkRef)] =
                (new AttributeRelation($junkRef, $junkAttribute['junctionTable'], $viaModel))
                    ->asHasMany([$junkAttribute['pairProperty'] . '_id' => $this->primaryKey]);
            return;
        }

        $this->resolveProperty($propertyName, $property, $isRequired);
    }

    protected function resolveProperty($propertyName, SpecObjectInterface $property, bool $isRequired)
    {
        $attribute = new Attribute($propertyName);
        $attribute->setRequired($isRequired)
            ->setDescription($property->description ?? '')
            ->setReadOnly($property->readOnly ?? false)
            ->setIsPrimary($propertyName === $this->primaryKey);
        if ($property instanceof Reference) {
            $refPointer = $property->getJsonReference()->getJsonPointer()->getPointer();
            $property->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $relatedSchema = $property->resolve();
            if (strpos($refPointer, self::REFERENCE_PATH) === 0) {
                if (strpos($refPointer, '/properties/') !== false) {
                    $relatedClassName = Inflector::id2camel($this->schemaName, '_');
                    $attribute->asReference($relatedClassName);
                    $foreignPk = $this->componentSchema->{CustomSpecAttr::PRIMARY_KEY} ?? 'id';
                    $foreignPkProperty = $this->componentSchema->properties[$foreignPk];
                    $relatedTableName = $this->tableName;
                    $phpType = SchemaTypeResolver::schemaToPhpType($foreignPkProperty);
                    $attribute->setPhpType($phpType)
                        ->setDbType($this->guessDbType($foreignPkProperty, true, true));
                    $attribute->setSize($foreignPkProperty->maxLength ?? null);
                    [$min, $max] = $this->guessMinMax($foreignPkProperty);
                    $attribute->setLimits($min, $max, $foreignPkProperty->minLength ?? null);

                    $relation = (new AttributeRelation($propertyName, $relatedTableName, $relatedClassName))
                        ->asHasOne([$foreignPk => $attribute->columnName])->asSelfReference();
                    $this->relations[$propertyName] = $relation;
                } else {
                    $relatedClassName = substr($refPointer, self::REFERENCE_PATH_LEN);
                    $relatedClassName = Inflector::id2camel($relatedClassName, '_');
                    $relatedTableName =
                        $relatedSchema->{CustomSpecAttr::TABLE} ?? self::tableNameBySchema($relatedClassName);
                    $attribute->asReference($relatedClassName)->setDescription($relatedSchema->description ?? '');
                    /**
                     * TODO: We need to detect primary key name of related column if it is not "id"
                     * So we should declare custom pk name in schema if it is not id
                     **/
                    $foreignPk = $relatedSchema->{CustomSpecAttr::PRIMARY_KEY} ?? 'id';
                    $foreignPkProperty = $relatedSchema->properties[$foreignPk];
                    if ($foreignPkProperty === null) {
                        //Non-db
                        return;
                    }
                    $phpType = SchemaTypeResolver::schemaToPhpType($foreignPkProperty);
                    $attribute->setPhpType($phpType)
                        ->setDbType($this->guessDbType($foreignPkProperty, true, true));
                    $attribute->setSize($foreignPkProperty->maxLength ?? null);
                    [$min, $max] = $this->guessMinMax($foreignPkProperty);
                    $attribute->setLimits($min, $max, $foreignPkProperty->minLength ?? null);
                    $relation = (new AttributeRelation($propertyName, $relatedTableName, $relatedClassName))
                        ->asHasOne([$foreignPk => $attribute->columnName]);
                    $this->relations[$propertyName] = $relation;
                }
            }
        }

        if (!$attribute->isReference()) {
            /**@var Schema $property */
            $phpType = SchemaTypeResolver::schemaToPhpType($property);
            if (isset($property->{CustomSpecAttr::DB_TYPE}) && $property->{CustomSpecAttr::DB_TYPE} === false) {
                if ($attribute->primary === true) {
                    throw new InvalidDefinitionException("Primary key can't be virtual attribute");
                }
                $attribute->setIsVirtual();
            }
            $attribute->setPhpType($phpType)
                ->setDbType($this->guessDbType($property, ($propertyName === $this->primaryKey)))
                ->setSize($property->maxLength ?? null)
                ->setDefault($this->guessDefault($property, $attribute));
            [$min, $max] = $this->guessMinMax($property);
            $attribute->setLimits($min, $max, $property->minLength ?? null);

            if (isset($property->enum) && is_array($property->enum)) {
                $attribute->setEnumValues($property->enum);
            }
        }

        // has Many relation
        $refPointer = $this->getHasManyReference($property);
        if ($refPointer !== null) {
            if ($attribute->isVirtual) {
                throw new InvalidDefinitionException('References not supported for virtual attributes');
            }
            //self relation
            if (strpos($refPointer, '/properties/') !== false) {
                $relatedClassName = Inflector::id2camel($this->schemaName, '_');
                $attribute->setPhpType($relatedClassName . '[]');
                $relatedTableName = $this->tableName;
                $foreignAttr = str_replace(self::REFERENCE_PATH . $relatedClassName . '/properties/', '', $refPointer);
                $foreignProperty = $this->componentSchema->properties[$foreignAttr] ?? null;
                if ($foreignProperty && !$foreignProperty instanceof Reference && !StringHelper::endsWith($foreignAttr, '_id')) {
                    $this->relations[$propertyName] =
                        (new AttributeRelation($propertyName, $relatedTableName, $relatedClassName))
                            ->asHasMany([$foreignAttr => $foreignAttr])->asSelfReference();
                    return;
                }
                $foreignPk = Inflector::camel2id($foreignAttr, '_') . '_id';
                $this->relations[$propertyName] =
                    (new AttributeRelation($propertyName, $relatedTableName, $relatedClassName))
                        ->asHasMany([$foreignPk => $this->primaryKey]);
                return;
            }
            $relatedSchemaName = substr($refPointer, self::REFERENCE_PATH_LEN);
            $relatedClassName = Inflector::id2camel($relatedSchemaName, '_');
            $property->items->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $relatedSchema = $property->items->resolve();
            $relatedTableName =
                $relatedSchema->{CustomSpecAttr::TABLE} ?? self::tableNameBySchema($relatedClassName);
            if ($this->catchManyToMany($propertyName, $relatedSchemaName, $relatedTableName, $relatedSchema)) {
                return;
            }

//            $foreignPk = $relatedSchema->{CustomSpecAttr::PRIMARY_KEY} ?? 'id';
            $attribute->setPhpType($relatedClassName . '[]');
            $this->relations[$propertyName] =
                (new AttributeRelation($propertyName, $relatedTableName, $relatedClassName))
                    ->asHasMany([Inflector::camel2id($this->schemaName, '_') . '_id' => $this->primaryKey]);
            return;
        }
        $this->attributes[$propertyName] = $attribute->setFakerStub($this->guessFakerStub($attribute, $property));
    }

    /**
     * Check and register many-to-many relation
     * - property name for many-to-many relation should be equal lower-cased, pluralized schema name
     * - referenced schema should contains mirrored reference to current schema
     * @param string $propertyName
     * @param string $relatedSchemaName
     * @param string $relatedTableName
     * @param \cebe\openapi\spec\Schema $relatedSchema
     * @return bool
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    protected function catchManyToMany(
        string $propertyName,
        string $relatedSchemaName,
        string $relatedTableName,
        Schema $relatedSchema
    ): bool {
        if (strtolower(Inflector::id2camel($propertyName, '_'))
            !== strtolower(Inflector::pluralize($relatedSchemaName))) {
            return false;
        }
        $expectedPropertyName = strtolower(Inflector::pluralize(Inflector::camel2id($this->schemaName, '_')));
        if (!isset($relatedSchema->properties[$expectedPropertyName])) {
            return false;
        }
        $relatedProperty = $relatedSchema->properties[$expectedPropertyName];
        $ref = $this->getHasManyReference($relatedProperty);
        $refClassName = substr($ref, self::REFERENCE_PATH_LEN);
        if ($refClassName !== $this->schemaName) {
            return false;
        }
        $relation = new ManyToManyRelation([
            'name' => $propertyName,
            'schemaName' => $this->schemaName,
            'relatedSchemaName' => $relatedSchemaName,
            'tableName' => $this->tableName,
            'relatedTableName' => $relatedTableName,
            'pkAttribute' => $this->attributes[$this->primaryKey],
        ]);
        $this->many2many[$propertyName] = $relation;
        return true;
    }

    protected function getHasManyReference(SpecObjectInterface $property): ?string
    {
        if ($property instanceof Reference) {
            return null;
        }
        if ($property->type === 'array' && isset($property->items) && $property->items instanceof Reference) {
            $ref = $property->items->getJsonReference()->getJsonPointer()->getPointer();
            if (strpos($ref, self::REFERENCE_PATH) === 0) {
                return $ref;
            }
        }
        return null;
    }

    protected function guessMinMax(SpecObjectInterface $property): array
    {
        $min = $property->minimum ?? null;
        $max = $property->maximum ?? null;
        if ($min !== null && $property->exclusiveMinimum) {
            $min++; //Need for ensure
        }
        if ($max !== null && $property->exclusiveMaximum) {
            $max++;
        }
        return [$min, $max];
    }

    protected function guessFakerStub(Attribute $attribute, SpecObjectInterface $property): ?string
    {
        $resolver = Yii::createObject(['class' => FakerStubResolver::class], [$attribute, $property]);
        return $resolver->resolve();
    }

    protected function guessDbType(Schema $property, bool $isPk, bool $isReference = false): string
    {
        if ($isReference === true) {
            return SchemaTypeResolver::referenceToDbType($property);
        }
        return SchemaTypeResolver::schemaToDbType($property, $isPk);
    }

    protected function guessDefault(Schema $property, Attribute $attribute)
    {
        if (!isset($property->default)) {
            return null;
        }

        if ($attribute->phpType === 'array' && in_array($property->default, ['{}', '[]'])) {
            return [];
        }
        if (is_string($property->default)
            && $attribute->phpType === 'array'
            && StringHelper::startsWith($attribute->dbType, 'json')) {
            try {
                return Json::decode($property->default);
            } catch (Throwable $e) {
                return [];
            }
        }

        if ($attribute->phpType === 'integer' && $property->default !== null) {
            return (int)$property->default;
        }

        return $property->default;
    }

    /**
     * @param array $indexes
     * @return array|DbIndex[]
     * @throws \cebe\yii2openapi\lib\exceptions\InvalidDefinitionException
     */
    protected function prepareIndexes(array $indexes): array
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
            foreach ($props as $prop) {
                if (!isset($this->attributes[$prop])) {
                    throw new InvalidDefinitionException('Invalid index definition - property ' . $prop . ' not declared');
                }
                $columns[] = $this->attributes[$prop]->columnName;
            }
            $dbIndex = DbIndex::make($this->tableName, $columns, $indexType, $unique);
            $dbIndexes[$dbIndex->name] = $dbIndex;
        }
        return $dbIndexes;
    }
}
