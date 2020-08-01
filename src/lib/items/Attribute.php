<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;
use yii\db\ColumnSchema;
use yii\helpers\Inflector;
use function is_array;
use function strtolower;

/**
 * @property-write mixed $default
 * @property-write bool  $isPrimary
 * @property-read string $formattedDescription
 */
class Attribute extends BaseObject
{
    /**
     * openApi schema property name
     * @var string
     */
    public $propertyName;

    /**
     * should be string/integer/boolean/float/double
     * @var string
     */
    public $phpType = 'string';

    /**
     * model/database column name
     * @var string
     */
    public $columnName;

    /**
     * should be one of \yii\db\Schema types or complete db column definition
     * @var string
     */
    public $dbType = 'string';

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
    public $unique = false;

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

    public function setUnique(bool $unique = true):Attribute
    {
        $this->unique = $unique;
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


    public function asReference(string $relatedClass):Attribute
    {
        $this->reference = $relatedClass;
        $this->columnName = $this->propertyName . '_id';
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

    public function isUnique():bool
    {
        return $this->unique;
    }

    public function isRequired():bool
    {
        return $this->required;
    }

    public function camelName():string
    {
        return Inflector::camelize($this->propertyName);
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
            'phpType'=>$this->phpType,
            'dbType' => strtolower($this->dbType),
            'type' => $this->dbTypeAbstract($this->dbType),
            'allowNull' => !$this->isRequired(),
            'size' => $this->size > 0 ? $this->size : null,
        ]);
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

        return $column;
    }

    private function dbTypeAbstract(string $type):string
    {
        if (stripos($type, 'int') === 0) {
            return 'integer';
        }
        if (stripos($type, 'string') === 0) {
            return 'string';
        }
        if (stripos($type, 'varchar') === 0) {
            return 'string';
        }
        if (stripos($type, 'json') === 0) {
            return 'json';
        }
        if (stripos($type, 'datetime') === 0) {
            return 'timestamp';
        }
        return $type;
    }
}
