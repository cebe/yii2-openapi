<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\openapi;

use BadMethodCallException;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\CustomSpecAttr;
use Throwable;
use Yii;
use yii\db\Schema as YiiDbSchema;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use function is_int;
use function strpos;
use function substr;

class PropertyReader
{
    public const REFERENCE_PATH = '/components/schemas/';
    public const REFERENCE_PATH_LEN = 20;

    /**
     * @var \cebe\openapi\SpecObjectInterface
     */
    private $property;

    /**@var string* */
    private $name;

    /**@var bool $isReference * */
    private $isReference = false;

    /**@var bool $isItemsReference * */
    private $isItemsReference = false;

    /**@var string $refPointer * */
    private $refPointer;

    /**@var \cebe\yii2openapi\lib\openapi\SchemaReader $refSchema * */
    private $refSchema;

    /**
     * @var bool
     */
    private $isPk;

    /**
     * @var \cebe\yii2openapi\lib\openapi\SchemaReader
     */
    private $schema;

    /**
     * @param \cebe\openapi\SpecObjectInterface          $property
     * @param string                                     $name
     * @param \cebe\yii2openapi\lib\openapi\SchemaReader $schema
     * @throws \yii\base\InvalidConfigException
     */
    public function __construct(SpecObjectInterface $property, string $name, SchemaReader $schema)
    {
        $this->name = $name;
        $this->property = $property;
        $this->schema = $schema;
        $this->isPk = $name === $schema->getPkName();

        if ($property instanceof Reference) {
            $this->initReference();
        } elseif (
            isset($property->type, $property->items) && $property->type === 'array'
            && $property->items instanceof Reference
        ) {
            $this->initItemsReference();
        }
        $this->schema = $schema;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function initReference():void
    {
        $this->isReference = true;
        $this->refPointer = $this->property->getJsonReference()->getJsonPointer()->getPointer();
        if ($this->isRefPointerToSelf()) {
            $this->refSchema = $this->schema;
        } elseif ($this->isRefPointerToSchema()) {
            $this->property->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $this->refSchema = Yii::createObject(SchemaReader::class, [$this->property->resolve()]);
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function initItemsReference():void
    {
        $this->isItemsReference = true;
        $items = $this->property->items ?? null;
        if (!$items) {
            return;
        }
        $this->refPointer = $items->getJsonReference()->getJsonPointer()->getPointer();
        if ($this->isRefPointerToSelf()) {
            $this->refSchema = $this->schema;
        } elseif ($this->isRefPointerToSchema()) {
            $items->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $this->refSchema = Yii::createObject(SchemaReader::class, [$items->resolve()]);
        }
    }

    public function setName(string $name):void
    {
        $this->name = $name;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function isPrimaryKey():bool
    {
        return $this->isPk;
    }

    public function getProperty():SpecObjectInterface
    {
        return $this->property;
    }

    public function getRefSchema():SchemaReader
    {
        if (!$this->isReference && !$this->isItemsReference) {
            throw new BadMethodCallException('Schema is not reference');
        }
        return $this->refSchema;
    }

    public function getTargetProperty():?PropertyReader
    {
        return $this->getRefSchema()->getProperty($this->getRefSchema()->getPkName());
    }

    public function getSelfTargetProperty():?PropertyReader
    {
        if (!$this->isRefPointerToSelf()) {
            return null;
        }
        $propName = str_replace(
            self::REFERENCE_PATH . $this->getRefClassName() . '/properties/',
            '',
            $this->refPointer
        );
        return $this->getRefSchema()->getProperty($propName);
    }

    public function isRefPointerToSchema():bool
    {
        return $this->refPointer && strpos($this->refPointer, self::REFERENCE_PATH) === 0;
    }

    public function isRefPointerToSelf():bool
    {
        return $this->isRefPointerToSchema() && strpos($this->refPointer, '/properties/') !== false;
    }



    public function getRefSchemaName():string
    {
        if (!$this->isReference && !$this->isItemsReference) {
            throw new BadMethodCallException('Property should be a reference or contains items with reference');
        }
        $name = substr($this->refPointer, self::REFERENCE_PATH_LEN);
        return $this->isRefPointerToSelf() ? substr($name, 0, strpos($name, '/properties/')) : $name;
    }

    public function getRefClassName():string
    {
        return Inflector::id2camel($this->getRefSchemaName(), '_');
    }

    public function getAttr(string $attrName, $default = null)
    {
        return $this->property->$attrName ?? $default;
    }

    public function hasAttr(string $attrName):bool
    {
        return isset($this->property->$attrName);
    }

    public function isReference():bool
    {
        return $this->isReference;
    }

    public function hasItems():bool
    {
        return !$this->isReference && isset($this->property->items, $this->property->type)
            && $this->property->type === 'array';
    }

    public function hasRefItems():bool
    {
        return $this->isItemsReference;
    }

    public function hasEnum():bool
    {
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        return isset($this->property->enum) && is_array($this->property->enum);
    }

    public function isVirtual():bool
    {
        return isset($this->property->{CustomSpecAttr::DB_TYPE})
            && $this->property->{CustomSpecAttr::DB_TYPE} === false;
    }

    public function guessMinMax():array
    {
        $min = $this->getAttr('minimum');
        $max = $this->getAttr('maximum');
        $exclusiveMin = $this->getAttr('exclusiveMinimum', false);
        $exclusiveMax = $this->getAttr('exclusiveMaximum', false);
        /**
         * @see OpenApi v.3.0 and v3.1 difference for exclusiveMinimum and exclusiveMaximum
         * https://apisyouwonthate.com/blog/openapi-v31-and-json-schema
         * (both variants supported)
         */
        if (is_int($exclusiveMin)) {
            $min = $exclusiveMin;
        }
        if (is_int($exclusiveMax)) {
            $max = $exclusiveMax;
        }
        if ($min !== null && $exclusiveMin === true) {
            $min++;
        }
        if ($max !== null && $exclusiveMax === true) {
            $max--;
        }

        return [$min, $max];
    }

    public function getMaxLength():?int
    {
        return $this->getAttr('maxLength');
    }

    public function getMinLength():?int
    {
        return $this->getAttr('minLength');
    }

    public function isReadonly():bool
    {
        return $this->getAttr('readOnly', false);
    }

    public function guessPhpType():string
    {
        $customDbType = isset($this->property->{CustomSpecAttr::DB_TYPE})
            ? strtolower($this->property->{CustomSpecAttr::DB_TYPE}) : null;
        if ($customDbType !== null
            && (in_array($customDbType, ['json', 'jsonb'], true) || StringHelper::endsWith($customDbType, '[]'))
        ) {
            return 'array';
        }

        switch ($this->getAttr('type')) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            case 'number': // can be double and float
                return $this->getAttr('format') === 'double' ? 'double' : 'float';
//            case 'array':
//                return $property->type;
            default:
                return $this->getAttr('type', 'string');
        }
    }

    public function guessDbType($forReference = false):string
    {
        if ($forReference) {
            $format = $this->getAttr('format');
            if ($this->getAttr('type') === 'integer') {
                return $format === 'int64' ? YiiDbSchema::TYPE_BIGINT : YiiDbSchema::TYPE_INTEGER;
            }
            return $this->getAttr('type');
        }
        if ($this->hasRefItems()) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        if ($this->hasAttr(CustomSpecAttr::DB_TYPE) && $this->getAttr(CustomSpecAttr::DB_TYPE) !== false) {
            $customDbType = strtolower($this->getAttr(CustomSpecAttr::DB_TYPE));
            if ($customDbType === 'varchar') {
                return YiiDbSchema::TYPE_STRING;
            }
            if ($customDbType !== null) {
                return $customDbType;
            }
        }
        $format = $this->getAttr('format');
        $type = $this->getAttr('type');
        if ($this->isPk && $type === 'integer') {
            return $format === 'int64' ? YiiDbSchema::TYPE_BIGPK : YiiDbSchema::TYPE_PK;
        }

        switch ($type) {
            case 'boolean':
                return $type;
            case 'number': // can be double and float
                return $format ?? 'float';
            case 'integer':
                if ($format === 'int64') {
                    return YiiDbSchema::TYPE_BIGINT;
                }
                return YiiDbSchema::TYPE_INTEGER;
            case 'string':
                if (in_array($format, ['date', 'time', 'binary'])) {
                    return $format;
                }
                if ($this->hasAttr('maxLength') && (int)$this->getAttr('maxLength') < 2049) {
                    //What if we want to restrict length of text column?
                    return YiiDbSchema::TYPE_STRING;
                }
                if ($format === 'date-time' || $format === 'datetime') {
                    return YiiDbSchema::TYPE_DATETIME;
                }
                if (in_array($format, ['email', 'url', 'phone', 'password'])) {
                    return YiiDbSchema::TYPE_STRING;
                }
                if (!empty($this->property->enum)) {
                    return YiiDbSchema::TYPE_STRING;
                }
                return YiiDbSchema::TYPE_TEXT;
            case 'object':
            {
                return YiiDbSchema::TYPE_JSON;
            }
//            case 'array':
//                Need schema example for this case if it is possible
//                return $this->typeForArray();
            default:
                return YiiDbSchema::TYPE_TEXT;
        }
    }

    /**
     * @return array|int|mixed|null
     */
    public function guessDefault()
    {
        if (!$this->hasAttr('default')) {
            return null;
        }
        $phpType = $this->guessPhpType();
        $dbType = $this->guessDbType();
        $default = $this->getAttr('default');

        if ($phpType === 'array' && in_array($default, ['{}', '[]'])) {
            return [];
        }
        if (is_string($default) && $phpType === 'array' && StringHelper::startsWith($dbType, 'json')) {
            try {
                return Json::decode($default);
            } catch (Throwable $e) {
                return [];
            }
        }

        if ($phpType === 'integer' && $default !== null) {
            return (int)$default;
        }

        return $default;
    }
}
