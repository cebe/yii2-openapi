<?php

namespace cebe\yii2openapi\lib\openapi;

use BadMethodCallException;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Reference;
use cebe\openapi\SpecObjectInterface;
use cebe\yii2openapi\lib\CustomSpecAttr;
use Throwable;
use yii\db\Schema as YiiDbSchema;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;

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

    /**@var string $refPointer * */
    private $refPointer;

    /**@var \cebe\yii2openapi\lib\openapi\SchemaReader $refSchema * */
    private $refSchema;

    /**
     * @var bool
     */
    private $isPk;

    public function __construct(SpecObjectInterface $property, string $name, bool $isPk = false)
    {
        if ($property instanceof Reference) {
            $this->refPointer = $property->getJsonReference()->getJsonPointer()->getPointer();
            $property->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
            $this->refSchema = new SchemaReader($property->resolve());
        }
        $this->name = $name;
        $this->property = $property;
        $this->isPk = $isPk;
    }

    public function setName(string $name):void
    {
        $this->name = $name;
    }

    public function getName(): string
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
        if (!$this->isReference) {
            throw new BadMethodCallException('Schema is not reference');
        }
        return $this->refSchema;
    }

    public function getForeighnProperty(): PropertyReader
    {
        if (!$this->isReference) {
            throw new BadMethodCallException('Schema is not reference');
        }
        return $this->getRefSchema()->getProperty($this->getRefSchema()->getPkName());
    }

    public function isRefPointerToSchema():bool
    {
        return $this->refPointer && strpos($this->refPointer, self::REFERENCE_PATH) === 0;
    }

    public function isRefPointerToSelf():bool
    {
        return $this->isRefPointerToSchema() && strpos($this->refPointer, '/properties/') !== false;
    }

    public function getSchemaNameByReference():string
    {
        if (!$this->isReference) {
            throw new BadMethodCallException('Property should be a reference');
        }
        return substr($this->refPointer, self::REFERENCE_PATH_LEN);
    }

    public function getClassNameByReference():string
    {
        return Inflector::id2camel($this->getSchemaNameByReference(), '_');
    }

    public function getPropertyAttr(string $attrName, $default = null)
    {
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        return isset($this->property->$attrName) ? $this->property->$attrName : $default;
    }

    public function isReference():bool
    {
        return $this->isReference;
    }

    public function hasItems():bool
    {
        return !$this->isReference && $this->property->type === 'array' && isset($this->property->items);
    }

    public function hasRefItems():bool
    {
        return $this->hasItems() && $this->property->items instanceof Reference;
    }

    public function getItemsRefPointer():string
    {
        return $this->hasRefItems() ? $this->property->items->getJsonReference()->getJsonPointer()->getPointer() : '';
    }

    public function getItemsRefSchema():SchemaReader
    {
        if (!$this->hasRefItems()) {
            throw new BadMethodCallException('Property hasn`t ref items');
        }
        $this->property->items->getContext()->mode = ReferenceContext::RESOLVE_MODE_ALL;
        return new SchemaReader($this->property->items->resolve());
    }

    public function getForeighnItemsProperty(): PropertyReader
    {
        if (!$this->hasRefItems()) {
            throw new BadMethodCallException('Schema hasn`t referenced items');
        }
        return $this->getItemsRefSchema()->getProperty($this->getItemsRefSchema()->getPkName());
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
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        return isset($this->property->{CustomSpecAttr::DB_TYPE})
            && $this->property->{CustomSpecAttr::DB_TYPE} === false;
    }

    public function guessMinMax():array
    {
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        $min = $this->property->minimum ?? null;
        $max = $this->property->maximum ?? null;
        if ($min !== null && $this->property->exclusiveMinimum) {
            $min++; //Need for ensure
        }
        if ($max !== null && $this->property->exclusiveMaximum) {
            $max++;
        }
        return [$min, $max];
    }

    public function getMaxLength():?int
    {
        return $this->getPropertyAttr('maxLength', null);
    }

    public function getMinLength():?int
    {
        return $this->getPropertyAttr('minLength', null);
    }

    public function isReadonly():bool
    {
        return $this->getPropertyAttr('readOnly', false);
    }

    public function guessPhpType():string
    {
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        $customDbType = isset($this->property->{CustomSpecAttr::DB_TYPE})
            ? strtolower($this->property->{CustomSpecAttr::DB_TYPE}) : null;
        if ($customDbType !== null
            && (in_array($customDbType, ['json', 'jsonb'], true) || StringHelper::endsWith($customDbType, '[]'))
        ) {
            return 'array';
        }

        switch ($this->property->type) {
            case 'integer':
                return 'int';
            case 'boolean':
                return 'bool';
            case 'number': // can be double and float
                return $this->property->format && $this->property->format === 'double' ? 'double' : 'float';
//            case 'array':
//                return $property->type;
            default:
                return $this->property->type ?? 'string';
        }
    }

    public function guessDbType():string
    {
        if ($this->isReference) {
            $format = $this->property->format ?? null;
            if ($this->property->type === 'integer') {
                return $format === 'int64' ? YiiDbSchema::TYPE_BIGINT : YiiDbSchema::TYPE_INTEGER;
            }
            return $this->property->type;
        }
        if (isset($this->property->{CustomSpecAttr::DB_TYPE}) && $this->property->{CustomSpecAttr::DB_TYPE} !== false) {
            $customDbType = strtolower($this->property->{CustomSpecAttr::DB_TYPE});
            if ($customDbType === 'varchar') {
                return YiiDbSchema::TYPE_STRING;
            }
            if ($customDbType !== null) {
                return $customDbType;
            }
        }
        $format = $this->property->format ?? null;
        if ($this->isPk && $this->property->type === 'integer') {
            return $format === 'int64' ? YiiDbSchema::TYPE_BIGPK : YiiDbSchema::TYPE_PK;
        }

        switch ($this->property->type) {
            case 'boolean':
                return $this->property->type;
            case 'number': // can be double and float
                return $format ?? 'float';
            case 'integer':
                if ($format === 'int64') {
                    return YiiDbSchema::TYPE_BIGINT;
                }
                if ($format === 'int32') {
                    return YiiDbSchema::TYPE_INTEGER;
                }
                return YiiDbSchema::TYPE_INTEGER;
            case 'string':
                if (in_array($format, ['date', 'time', 'binary'])) {
                    return $format;
                }
                if ($this->property->maxLength && $this->property->maxLength < 2049) {
                    //What if we want to restrict length of text column?
                    return YiiDbSchema::TYPE_STRING;
                }
                if ($format === 'date-time' || $format === 'datetime') {
                    return YiiDbSchema::TYPE_DATETIME;
                }
                if (in_array($format, ['email', 'url', 'phone', 'password'])) {
                    return YiiDbSchema::TYPE_STRING;
                }
                if (isset($this->property->enum) && !empty($this->property->enum)) {
                    return YiiDbSchema::TYPE_STRING;
                }
                return YiiDbSchema::TYPE_TEXT;
            case 'object':
            {
                return YiiDbSchema::TYPE_JSON;
            }
//            case 'array':
//                Need schema example for this case if it possible
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
        if ($this->isReference) {
            throw new BadMethodCallException('Not supported for referenced property');
        }
        if (!isset($this->property->default)) {
            return null;
        }
        $phpType = $this->guessPhpType();
        $dbType = $this->guessDbType();

        if ($phpType === 'array' && in_array($this->property->default, ['{}', '[]'])) {
            return [];
        }
        if (is_string($this->property->default) && $phpType === 'array' && StringHelper::startsWith($dbType, 'json')) {
            try {
                return Json::decode($this->property->default);
            } catch (Throwable $e) {
                return [];
            }
        }

        if ($phpType === 'integer' && $this->property->default !== null) {
            return (int)$this->property->default;
        }

        return $this->property->default;
    }
}