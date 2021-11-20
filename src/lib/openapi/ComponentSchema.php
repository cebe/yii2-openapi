<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\openapi;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\CustomSpecAttr;
use Generator;
use Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function in_array;

class ComponentSchema
{
    /**
     * @var \cebe\openapi\spec\Schema
     */
    private $schema;

    /**
     * @var bool
     */
    private $isReference = false;

    /**
     * @var string
     */
    private $pkName;

    /**@var array* */
    private $requiredProps;

    /**@var array* */
    private $indexes;

    public function __construct(SpecObjectInterface $openApiSchema)
    {
        if ($openApiSchema instanceof Reference) {
            $this->isReference = true;
            $openApiSchema->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $this->schema = $openApiSchema->resolve();
        } else {
            $this->schema = $openApiSchema;
        }
        $this->pkName = $openApiSchema->{CustomSpecAttr::PRIMARY_KEY} ?? 'id';
        $this->requiredProps = $openApiSchema->required ?? [];
        $this->indexes = $openApiSchema->{CustomSpecAttr::INDEXES} ?? [];
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function isReference():bool
    {
        return $this->isReference;
    }

    public function isObjectSchema():bool
    {
        return (empty($this->schema->type) || $this->schema->type === 'object');
    }

    public function isCompositeSchema():bool
    {
        return $this->schema->allOf || $this->schema->anyOf || $this->schema->multipleOf || $this->schema->oneOf;
    }

    public function getPkName():string
    {
        return $this->pkName;
    }

    public function getRequiredProperties():array
    {
        return $this->requiredProps;
    }

    public function isRequiredProperty(string $propName):bool
    {
        return in_array($propName, $this->requiredProps, true);
    }

    public function isNonDb():bool
    {
        return isset($this->schema->{CustomSpecAttr::TABLE}) && $this->schema->{CustomSpecAttr::TABLE} === false;
    }

    public function resolveTableName(string $schemaName):string
    {
        return $this->schema->{CustomSpecAttr::TABLE} ??
            Inflector::camel2id(StringHelper::basename(Inflector::pluralize($schemaName)), '_');
    }

    public function hasCustomTableName():bool
    {
        return isset($this->schema->{CustomSpecAttr::TABLE});
    }

    public function getIndexes():array
    {
        return $this->schema->{CustomSpecAttr::INDEXES} ?? [];
    }

    public function getDescription():string
    {
        return $this->schema->description ?? '';
    }

    public function hasProperties():bool
    {
        return !empty($this->schema->properties);
    }

    public function hasProperty(string $name):bool
    {
        return isset($this->schema->properties[$name]);
    }

    public function getProperty(string $name):?PropertySchema
    {
        if (!$this->hasProperty($name)) {
            return null;
        }
        return Yii::createObject(PropertySchema::class, [$this->schema->properties[$name], $name, $this]);
    }

    /**
     * @return \Generator|\cebe\yii2openapi\lib\openapi\PropertySchema[]
     * @throws \yii\base\InvalidConfigException
     */
    public function getProperties():Generator
    {
        foreach ($this->schema->properties as $name => $property) {
            yield Yii::createObject(PropertySchema::class, [$property, $name, $this]);
        }
    }
}
