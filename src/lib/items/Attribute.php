<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\helpers\VarDumper;
use \Yii;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\exceptions\InvalidDefinitionException;
use yii\base\BaseObject;
use cebe\yii2openapi\db\ColumnSchema;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\db\mysql\Schema as MySqlSchema;
use SamIT\Yii2\MariaDb\Schema as MariaDbSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\base\NotSupportedException;
use function is_array;
use function strtolower;

/**
 * @property-write mixed $default
 * @property-write bool  $isPrimary
 * @property-read string $formattedDescription
 * @property-read null|int $maxLength
 * @property-read null|int $minLength
 */
class Attribute extends BaseObject
{
    /**
     * openApi schema property name
     * @var string
     */
    public $propertyName;

    /**
     * should be string/integer/boolean/float/double/array
     * @var string
     */
    public $phpType = 'string';

    /**
     * model/database column name
     * @var string
     */
    public $columnName;

    /**
     * @var string
     * Contains foreign key column name
     * @example 'redelivery_of'
     * See usage docs in README for more info
     */
    public $fkColName;

    /**
     * should be one of \yii\db\Schema types or complete db column definition
     * @var string
     */
    public $dbType = 'string';

    /**
     * Custom db type
     * string | null | false
     * if `false` then this attribute is virtual
     */
    public $xDbType;

    /**
     * nullable
     * bool | null
     */
    public $nullable;

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $readOnly = false;

    /**
     * @var bool
     */
    public $required = false;

    /**
     * related object name, if it exists
     * @var string
     */
    public $reference;
    /**
     * @var int|null (db field length)
     **/
    public $size;

    public $limits = ['min' => null, 'max' => null, 'minLength' => null];

    /**
     * @var bool
     */
    public $primary = false;

    /**
     * @var mixed
     */
    public $defaultValue;

    /**
     * @var array|null
     */
    public $enumValues;

    /**
     * @var string|null
     **/
    public $fakerStub;

    /**
     * @var bool
     **/
    public $isVirtual = false;

    public function __construct(string $propertyName, array $config = [])
    {
        $this->propertyName = $propertyName;
        $this->columnName = $propertyName; // force camel2id ?
        parent::__construct($config);
    }

    public function setPhpType(string $phpType):Attribute
    {
        $this->phpType = $phpType;
        return $this;
    }

    public function setDbType(string $dbType):Attribute
    {
        $this->dbType = $dbType;
        return $this;
    }

    public function setXDbType($xDbType):Attribute
    {
        $this->xDbType = $xDbType;
        return $this;
    }

    public function setXDbDefaultExpression($xDbDefaultExpression): Attribute
    {
        // first priority is given to `default` and then to `x-db-default-expression`
        if ($xDbDefaultExpression !== null && $this->defaultValue === null) {
            $this->defaultValue = new \yii\db\Expression('('.$xDbDefaultExpression.')');
        }
        return $this;
    }

    public function setNullable($nullable):Attribute
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function setDescription(string $description):Attribute
    {
        $this->description = $description;
        return $this;
    }

    public function setReadOnly(bool $readOnly = true):Attribute
    {
        $this->readOnly = $readOnly;
        return $this;
    }

    public function setRequired(bool $required = true):Attribute
    {
        $this->required = $required;
        return $this;
    }

    public function setSize(?int $size):Attribute
    {
        $this->size = $size;
        return $this;
    }

    public function setDefault($value):Attribute
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function setEnumValues(array $values):Attribute
    {
        $this->enumValues = $values;
        return $this;
    }

    public function setForeignKeyColumnName(?string $name):Attribute
    {
        if ($name) {
            $this->fkColName = $name;
        }
        return $this;
    }

    /**
     * @param int|float|null $min
     * @param int|float|null $max
     * @param int|null       $minLength
     * @return $this
     */
    public function setLimits($min, $max, ?int $minLength):Attribute
    {
        $this->limits = ['min' => $min, 'max' => $max, 'minLength' => $minLength];
        return $this;
    }

    public function setFakerStub(?string $fakerStub):Attribute
    {
        $this->fakerStub = $fakerStub;
        return $this;
    }

    public function setIsPrimary(bool $isPrimary = true):Attribute
    {
        $this->primary = $isPrimary;
        return $this;
    }

    public function setIsVirtual(bool $isVirtual = true): Attribute
    {
        $this->isVirtual = $isVirtual;
        return $this;
    }


    public function asReference(string $relatedClass):Attribute
    {
        $this->reference = $relatedClass;
        $this->columnName = $this->fkColName ?
            $this->fkColName :
            $this->propertyName . '_id';
        return $this;
    }

    public function asNonDbReference(string $relatedClass):Attribute
    {
        $this->reference = $relatedClass;
        $this->columnName = $this->propertyName;
        return $this;
    }

    public function isReadOnly():bool
    {
        return $this->readOnly;
    }

    public function isReference():bool
    {
        return $this->reference !== null;
    }

    public function isRequired():bool
    {
        return $this->required;
    }

    public function isVirtual():bool
    {
        return $this->isVirtual;
    }

    public function camelName():string
    {
        return Inflector::camelize($this->propertyName);
    }

    public function getMaxLength():?int
    {
        return $this->size;
    }

    public function getMinLength():?int
    {
        return $this->limits['minLength'];
    }

    public function getFormattedDescription():string
    {
        $comment = $this->columnName.' '.$this->description;
        $type = $this->phpType;
        return $type.' $'.str_replace("\n", "\n * ", rtrim($comment));
    }

    public function toColumnSchema():ColumnSchema
    {
        $column = new ColumnSchema([
            'name' => $this->columnName,
            'phpType'=> $this->phpType,
            'dbType' => strtolower($this->dbType),
            'type' => $this->yiiAbstractTypeForDbSpecificType($this->dbType),
            'allowNull' => $this->allowNull(),
            'size' => $this->size > 0 ? $this->size : null,
            'xDbType' => $this->xDbType,
        ]);
        $column->isPrimaryKey = $this->primary;
        $column->autoIncrement = $this->primary && $this->phpType === 'int';
        if ($column->type === 'json') {
            $column->allowNull = false;
        }
        if ($this->defaultValue !== null) {
            $column->defaultValue = $this->defaultValue;
        } elseif ($column->allowNull) {
            //@TODO: Need to discuss
            $column->defaultValue = null;
        }
        if (is_array($this->enumValues)) {
            $column->enumValues = $this->enumValues;
        }
        $this->handleDecimal($column);

        return $column;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    private function yiiAbstractTypeForDbSpecificType(string $dbType): string
    {
        if (is_string($this->xDbType) && !empty($this->xDbType) && trim($this->xDbType)) {
            list(, $yiiAbstractDataType, ) = PropertySchema::findMoreDetailOf($this->xDbType);
            return $yiiAbstractDataType;
        } else {
            if (stripos($dbType, 'int') === 0) {
                return 'integer';
            }
            if (stripos($dbType, 'string') === 0) {
                return 'string';
            }
            if (stripos($dbType, 'varchar') === 0) {
                return 'string';
            }
            if (stripos($dbType, 'tsvector') === 0) {
                return 'string';
            }
            if (stripos($dbType, 'json') === 0) {
                return 'json';
            }
            if (stripos($dbType, 'datetime') === 0) {
                return 'timestamp';
            }
        }

        return $dbType;
    }

    private function allowNull()
    {
        if (is_bool($this->nullable)) {
            return $this->nullable;
        }
        return !$this->isRequired();
    }

    public function handleDecimal(ColumnSchema $columnSchema): void
    {
        if ($decimalAttributes = \cebe\yii2openapi\lib\ColumnToCode::isDecimalByDbType($columnSchema->dbType)) {
            $columnSchema->precision = $decimalAttributes['precision'];
            $columnSchema->scale = $decimalAttributes['scale'];
            $columnSchema->dbType = $decimalAttributes['dbType'];
        }
    }
}
