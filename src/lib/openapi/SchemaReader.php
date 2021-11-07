<?php

namespace cebe\yii2openapi\lib\openapi;

use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\CustomSpecAttr;
use Generator;
use InvalidArgumentException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function in_array;

class SchemaReader
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

    public function __construct(SpecObjectInterface $schema)
    {
        if ($schema instanceof Reference) {
            $this->isReference = true;
            $schema->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $schema = $schema->resolve();
        }
        $this->schema = $schema;
        $this->pkName = $schema->{CustomSpecAttr::PRIMARY_KEY} ?? 'id';
        $this->requiredProps = $schema->required ?? [];
        $this->indexes = $schema->{CustomSpecAttr::INDEXES} ?? [];
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
        return in_array($propName, $this->requiredProps);
    }

    public function getTableName(string $schemaName):string
    {
        return $this->schema->{CustomSpecAttr::TABLE} ??
            Inflector::camel2id(StringHelper::basename(Inflector::pluralize($schemaName)), '_');
    }

    public function hasProperties():bool
    {
        return !empty($this->schema->properties);
    }

    public function hasProperty(string $name):bool
    {
        return isset($this->schema->properties[$name]);
    }

    public function getProperty(string $name):PropertyReader
    {
        if (!$this->hasProperty($name)) {
            throw new InvalidArgumentException("Property '$name' not exists");
        }
        return new PropertyReader($this->schema->properties[$name], $name, $name === $this->pkName);
    }

    public function getProperties():Generator
    {
        foreach ($this->schema->properties as $name => $property) {
            yield new PropertyReader($property, $name, $name === $this->pkName);
        }
    }
}