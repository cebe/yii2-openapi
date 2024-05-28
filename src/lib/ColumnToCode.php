<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use yii\db\ArrayExpression;
use cebe\yii2openapi\lib\migrations\BaseMigrationBuilder;
use cebe\yii2openapi\generator\ApiGenerator;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\db\Schema;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use function in_array;
use function is_string;
use function preg_replace;
use function sprintf;
use function stripos;
use function strpos;
use function strtolower;
use function trim;

class ColumnToCode
{
    public const PK_TYPE_MAP = [
        Schema::TYPE_PK => 'primaryKey()',
        Schema::TYPE_UPK => 'primaryKey()->unsigned()',
        Schema::TYPE_BIGPK => 'bigPrimaryKey()',
        Schema::TYPE_UBIGPK => 'bigPrimaryKey()->unsigned()',
    ];

    public const INT_TYPE_MAP = [
        Schema::TYPE_TINYINT => 'tinyInteger',
        Schema::TYPE_SMALLINT => 'smallInteger',
        Schema::TYPE_BIGINT => 'bigInteger',
    ];

    /**
     * @var \yii\db\ColumnSchema
     */
    private $column;

    /**
     * @var \yii\db\Schema
     */
    private $dbSchema;

    /**
     * @var string
     * @example {{%table}}
     */
    private $tableAlias;

    /**
     * @var bool
     */
    private $fromDb;

    /**
     * @var bool
     * Built In Type means the \cebe\yii2openapi\lib\items\Attribute::$type or \cebe\yii2openapi\lib\items\Attribute::$dbType is in list of Yii abstract data type list or not. And if is found we can use \yii\db\SchemaBuilderTrait methods to build migration instead of putting raw SQL
     */
    private $isBuiltinType = false;

    /**
     * @var bool
     * if set to `true`, `getCode()` method will return SQL migration in raw form instead of SchemaBuilderTrait methods
     * Example: `string null default null`
     * It won't return `$this->string()->null()->defaultValue(null)`
     */
    private $raw = false;

    /**
     * @var bool
     */
    private $isPk = false;

    private $rawParts = ['type' => null, 'nullable' => null, 'default' => null, 'position' => null];

    private $fluentParts = ['type' => null, 'nullable' => null, 'default' => null, 'position' => null];

    /**
     * @var bool
     */
    private $alter;

    /**
     * @var bool
     * Q. How does it differ from `$alter` and why it is needed?
     * A. Pgsql alter column data type does not support NULL, DEFAULT etc value. We just have to provide new data type.
     * NULL will be handled SET NULL/SET NOT NULL
     * DEFAULT will be handled by SET DEFAULT ...
     * This property is only used in Pgsql DB and for alter column data type cases
     */
    private $alterByXDbType;

    /**
     * @var null|string
     * Column name of previous column/field.
     * Used for `AFTER` in MySQL/Mariadb to preserve order as in OpenAPI schema defination.
     * Its possible values are:
     *      `'FIRST'`
     *      `'AFTER <columnName>'` e.g. AFTER first_name
     *      `null` (default) means append new column at the end
     */
    private $position;

    /**
     * ColumnToCode constructor.
     * @param \yii\db\Schema       $dbSchema
     * @param \yii\db\ColumnSchema $column
     * @param bool                 $fromDb (if from database we prefer column type for usage, from schema - dbType)
     * @param bool                 $alter (flag for resolve quotes that is different for create and alter)
     * @param bool                 $raw see @property $raw above for docs
     * @param bool                 $alterByXDbType see @alterByXDbType $raw above for docs
     */
    public function __construct(
        Schema $dbSchema,
        string $tableAlias,
        ColumnSchema $column,
        bool $fromDb = false,
        bool $alter = false,
        bool $raw = false,
        bool $alterByXDbType = false,
        ?string $position = null
    ) {
        $this->dbSchema = $dbSchema;
        $this->tableAlias = $tableAlias;
        $this->column = $column;
        $this->fromDb = $fromDb;
        $this->alter = $alter;
        $this->raw = $raw;
        $this->alterByXDbType = $alterByXDbType;
        $this->position = $position;

        // We use `property_exists()` because sometimes we can have instance of \yii\db\mysql\ColumnSchema (or of Maria/Pgsql) or \cebe\yii2openapi\db\ColumnSchema
        if (property_exists($this->column, 'xDbType') && is_string($this->column->xDbType) && !empty($this->column->xDbType)) {
            $this->raw = true;
        }

        $this->resolve();
    }

    public function getCode(bool $quoted = false):string
    {
        if ($this->isPk) {
            return '$this->' . $this->fluentParts['type'];
        }
        if ($this->isBuiltinType) {
            $parts = array_filter([
                $this->fluentParts['type'],
                $this->fluentParts['nullable'],
                $this->fluentParts['default'],
                $this->fluentParts['position']
            ]);
            array_unshift($parts, '$this');
            return implode('->', array_filter(array_map('trim', $parts)));
        }
        if ($this->rawParts['default'] === null) {
            $default = '';
        } elseif (ApiGenerator::isPostgres() && $this->isEnum()) {
            $default =
                $this->rawParts['default'] !== null ? ' DEFAULT ' . trim($this->rawParts['default']) : '';
        } else {
            $default = $this->rawParts['default'] !== null ? ' DEFAULT ' . trim($this->rawParts['default']) : '';
        }

        $code = $this->rawParts['type'] . ' ' . $this->rawParts['nullable'] . $default;
        if ((ApiGenerator::isMysql() || ApiGenerator::isMariaDb()) && $this->rawParts['position']) {
            $code .= ' ' . $this->rawParts['position'];
        }
        if (ApiGenerator::isPostgres() && $this->alterByXDbType) {
            return $quoted ? VarDumper::export($this->rawParts['type']) : $this->rawParts['type'];
        }
        return $quoted ? VarDumper::export($code) : $code;
    }

    public function getAlterExpression(bool $addUsingExpression = false):string
    {
        if ($this->isEnum() && ApiGenerator::isPostgres()) {
            $rawTableName = $this->dbSchema->getRawTableName($this->tableAlias);
            $enumTypeName = 'enum_'.$rawTableName.'_'.$this->column->name;
            return "'" . sprintf('"'.$enumTypeName.'" USING "%1$s"::"'.$enumTypeName.'"', $this->column->name) . "'";
        }
        if ($this->column->dbType === 'tsvector') {
            return "'" . $this->rawParts['type'] . "'";
        }
        if ($addUsingExpression && ApiGenerator::isPostgres()) {
            return "'" . $this->rawParts['type'] .
                ($this->alterByXDbType ?
                    '' :
                    " ".$this->rawParts['nullable'])
                .' USING "'.$this->column->name.'"::'.$this->typeWithoutSize($this->rawParts['type'])."'";
        }

        if (ApiGenerator::isPostgres() && $this->alterByXDbType) {
            return "'" . $this->rawParts['type'] . "'";
        }

        return $this->isBuiltinType
            ? '$this->' . $this->fluentParts['type'].'->'.$this->fluentParts['nullable']
            : "'" . $this->rawParts['type'] . " ".$this->rawParts['nullable']."'";
    }

    public function getDefaultValue():?string
    {
        return $this->rawParts['default'];
    }

    public function isJson():bool
    {
        return in_array(strtolower($this->column->dbType), ['json', 'jsonb'], true);
    }

    public function isEnum():bool
    {
        return BaseMigrationBuilder::isEnum($this->column);
    }

    public function isDecimal()
    {
        return self::isDecimalByDbType($this->column->dbType);
    }

    /**
     * @param $dbType
     * @return array|false
     */
    public static function isDecimalByDbType($dbType)
    {
        $precision = null;
        $scale = null;

        // https://runebook.dev/de/docs/mariadb/decimal/index
        $precisionDefault = 10;
        $scaleDefault = 2;

        preg_match_all('/(decimal\()+([0-9]+)+(,)+([0-9]+)+(\))/', $dbType, $matches);
        if (!empty($matches[4][0])) {
            $precision = $matches[2][0];
            $scale = $matches[4][0];
        }

        if (empty($precision)) {
            preg_match_all('/(decimal\()+([0-9]+)+(\))/', $dbType, $matches);
            if (!empty($matches[2][0])) {
                $precision = $matches[2][0];
                $scale = $scaleDefault;
            }
        }

        if (empty($precision)) {
            if (strtolower($dbType) === 'decimal') {
                $precision = $precisionDefault;
                $scale = $scaleDefault;
            }
        }

        if (empty($precision)) {
            return false;
        }

        return [
            'precision' => (int)$precision,
            'scale' => (int)$scale,
            'dbType' => "decimal($precision,$scale)",
        ];
    }

    public static function escapeQuotes(string $str):string
    {
        return str_replace(["'", '"', '$'], ["\\'", "\\'", '\$'], $str);
    }

    public static function undoEscapeQuotes(string $str):string
    {
        return str_replace(["\\'", "\\'", '\$'], ["'", '"', '$'], $str);
    }

    public static function wrapQuotes(string $str, string $quotes = "'", bool $escape = true):string
    {
        if ($escape && strpos($str, $quotes) !== false) {
            return $quotes . self::escapeQuotes($str) . $quotes;
        }
        return $quotes . $str . $quotes;
    }

    public static function enumToString(array $enum):string
    {
        $items = implode(", ", array_map(self::class.'::wrapQuotes', $enum));
        return self::escapeQuotes($items);
    }

    public static function mysqlEnumToString(array $enum):string
    {
        return implode(', ', array_map(function ($aEnumValue) {
            return self::wrapQuotes($aEnumValue, '"');
        }, $enum));
    }

    private function defaultValueJson(array $value):string
    {
        if ($this->alter === true) {
            return "'" . str_replace('"', '\"', Json::encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT)) . "'";
        }
        return "'" . Json::encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) . "'";
    }

    private function defaultValueArray(array $value):string
    {
        return "'{" . trim(Json::encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT), '[]') . "}'";
    }

    private function resolve():void
    {
        $dbType = $this->typeWithoutSize(strtolower($this->column->dbType));
        $type = $this->column->type;
        $this->resolvePosition();
        //Primary Keys
        if (array_key_exists($type, self::PK_TYPE_MAP)) {
            $this->rawParts['type'] = $type;
            $this->fluentParts['type'] = self::PK_TYPE_MAP[$type];
            $this->isPk = true;
            return;
        }
        if (array_key_exists($dbType, self::PK_TYPE_MAP)) {
            $this->rawParts['type'] = $dbType;
            $this->fluentParts['type'] = self::PK_TYPE_MAP[$dbType];
            $this->isPk = true;
            return;
        }

        if ($dbType === 'varchar') {
            $type = $dbType = 'string';
        }
        $fluentSize = $this->column->size ? '(' . $this->column->size . ')' : '()';
        $rawSize = $this->column->size ? '(' . $this->column->size . ')' : '';
        $this->rawParts['nullable'] = $this->column->allowNull ? 'NULL' : 'NOT NULL';
        $this->fluentParts['nullable'] = $this->column->allowNull === true ? 'null()' : 'notNull()';
        if (array_key_exists($dbType, self::INT_TYPE_MAP)) {
            $this->fluentParts['type'] = self::INT_TYPE_MAP[$dbType] . $fluentSize;
            $this->rawParts['type'] =
                $this->column->dbType . (strpos($this->column->dbType, '(') !== false ? '' : $rawSize);
        } elseif (array_key_exists($type, self::INT_TYPE_MAP)) {
            $this->fluentParts['type'] = self::INT_TYPE_MAP[$type] . $fluentSize;
            $this->rawParts['type'] =
                $this->column->dbType . (strpos($this->column->dbType, '(') !== false ? '' : $rawSize);
        } elseif ($this->isEnum()) {
            $this->resolveEnumType();
        } elseif ($this->isDecimal()) {
            $this->fluentParts['type'] = $this->column->dbType;
            $this->rawParts['type'] = $this->column->dbType;
        } else {
            $this->fluentParts['type'] = $type . $fluentSize;
            $this->rawParts['type'] =
                $this->column->dbType . (strpos($this->column->dbType, '(') !== false ? '' : $rawSize);
        }

        $this->isBuiltinType = $this->raw ? false : $this->getIsBuiltinType($type, $dbType);

        $this->resolveDefaultValue();
    }

    /**
     * @param $type
     * @param $dbType
     * @return bool
     */
    private function getIsBuiltinType($type, $dbType)
    {
        if (property_exists($this->column, 'xDbType') && is_string($this->column->xDbType) && !empty($this->column->xDbType)) {
            return false;
        }

        if ($this->isEnum()) {
            return false;
        }
        if ($this->fromDb === true) {
            return isset(
                (new ColumnSchemaBuilder(''))->categoryMap[$type]
            );
        } else {
            return isset(
                (new ColumnSchemaBuilder(''))->categoryMap[$dbType]
            );
        }
    }

    private function resolveEnumType():void
    {
        if (ApiGenerator::isPostgres()) {
            $rawTableName = $this->dbSchema->getRawTableName($this->tableAlias);
            $this->rawParts['type'] = '"enum_'.$rawTableName.'_' . $this->column->name.'"';
            return;
        }
        $this->rawParts['type'] = 'enum(' . self::mysqlEnumToString($this->column->enumValues) . ')';
    }

    private function resolveDefaultValue():void
    {
        if (!$this->isDefaultAllowed()) {
            return;
        }
        $value = $this->column->defaultValue;
        if ($value === null || (is_string($value) && (stripos($value, 'null::') !== false))) {
            $this->fluentParts['default'] = ($this->column->allowNull === true) ? 'defaultValue(null)' : $this->fluentParts['default'];
            $this->rawParts['default'] = ($this->column->allowNull === true) ? 'NULL' : $this->rawParts['default'];
            return;
        }
        $expectInteger = is_numeric($value) && stripos($this->column->dbType, 'int') !== false;
        switch (gettype($value)) {
            case 'integer':
                $this->fluentParts['default'] = 'defaultValue(' . $value . ')';
                $this->rawParts['default'] = $value;
                break;
            case 'double':
            case 'float':
                // ensure type cast always has . as decimal separator in all locales
                $value = str_replace(',', '.', (string)$value);
                $this->fluentParts['default'] = 'defaultValue("' . $value . '")';
                $this->rawParts['default'] = $value;
                break;
            case 'boolean':
                $this->fluentParts['default'] = (bool)$value === true ? 'defaultValue(true)' : 'defaultValue(false)';
                if (ApiGenerator::isPostgres()) {
                    $this->rawParts['default'] = ((bool)$value === true ? "'t'" : "'f'");
                } else {
                    $this->rawParts['default'] = ((bool)$value === true ? '1' : '0');
                }
                break;
            case 'object':
                if ($value instanceof JsonExpression) {
                    $this->fluentParts['default'] = "defaultValue('" . Json::encode($value->getValue(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) . "')";
                    $this->rawParts['default'] = $this->defaultValueJson($value->getValue());
                } elseif ($value instanceof ArrayExpression) {
                    $this->fluentParts['default'] = "defaultValue('" . Json::encode($value->getValue(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) . "')";
                    $this->rawParts['default'] = $this->defaultValueArray($value->getValue());
                } else {
                    // $value instanceof \yii\db\Expression
                    $this->fluentParts['default'] = 'defaultExpression("' . (string)$value . '")';
                    $this->rawParts['default'] = (string)$value;
                }
                break;
            case 'array':
                $this->fluentParts['default'] = "defaultValue('" . Json::encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT) . "')";
                $this->rawParts['default'] = $this->isJson()
                    ? $this->defaultValueJson($value)
                    : $this->defaultValueArray($value);
                break;
            default:
                $this->fluentParts['default'] = $expectInteger
                    ? 'defaultValue(' . $value . ')' : 'defaultValue(' . VarDumper::export((string)$value) . ')';
                $this->rawParts['default'] = $expectInteger ? $value : VarDumper::export((string)$value);
        }
    }

    private function isDefaultAllowed():bool
    {
        // default expression with parenthases is allowed
        if ($this->column->defaultValue instanceof \yii\db\Expression) {
            return true;
        }

        $type = strtolower($this->column->dbType);
        switch ($type) {
            case 'tsvector':
                return false;
            case 'blob':
            case 'geometry':
            case 'text':
            case 'json':
                if (ApiGenerator::isMysql()) {
                    // The BLOB, TEXT, GEOMETRY, and JSON data types cannot be assigned a literal default value.
                    // https://dev.mysql.com/doc/refman/8.0/en/data-type-defaults.html
                    return false;
                }
                return true;
            case 'enum':
            default:
                return true;
        }
    }

    private function typeWithoutSize(string $type):string
    {
        return preg_replace('~(.*)(\(\d+\))~', '$1', $type);
    }

    public function resolvePosition()
    {
        if (ApiGenerator::isMysql() || ApiGenerator::isMariaDb()) {
            if ($this->position === BaseMigrationBuilder::POS_FIRST) {
                $this->fluentParts['position'] = 'first()';
                $this->rawParts['position'] = BaseMigrationBuilder::POS_FIRST;
            } elseif ($this->position !== null && strpos($this->position, BaseMigrationBuilder::POS_AFTER.' ') !== false) {
                $previousColumn = str_replace(BaseMigrationBuilder::POS_AFTER.' ', '', $this->position);
                $this->fluentParts['position'] = 'after(\''.$previousColumn.'\')';
                $this->rawParts['position'] = BaseMigrationBuilder::POS_AFTER.' '.$previousColumn;
            }
        }
    }
}
