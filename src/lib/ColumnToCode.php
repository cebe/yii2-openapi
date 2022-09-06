<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use yii\db\ArrayExpression;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\db\Schema;
use yii\helpers\Json;
use yii\helpers\StringHelper;
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
     * @var bool
     */
    private $fromDb;

    /**
     * @var bool
     */
    private $isBuiltinType = false;

    /**
     * @var bool
     */
    private $isPk = false;

    private $rawParts = ['type' => null, 'nullable' => null, 'default' => null, 'after' => null];

    private $fluentParts = ['type' => null, 'nullable' => null, 'default' => null, 'after' => null];

    /**
     * @var bool
     */
    private $alter;

    /**
     * Used for `AFTER` in SQL to preserve order as in OpenAPI schema
     */
    private ?string $previousColumnName = null;

    /**
     * ColumnToCode constructor.
     * @param \yii\db\Schema       $dbSchema
     * @param \yii\db\ColumnSchema $column
     * @param bool                 $fromDb (if from database we prefer column type for usage, from schema - dbType)
     * @param bool                 $alter (flag for resolve quotes that is different for create and alter)
     */
    public function __construct(
        Schema $dbSchema,
        ColumnSchema $column,
        bool $fromDb = false,
        bool $alter = false,
        ?string $previousColumnName = null
    )
    {
        $this->dbSchema = $dbSchema;
        $this->column = $column;
        $this->fromDb = $fromDb;
        $this->alter = $alter;
        $this->previousColumnName = $previousColumnName;
        $this->resolve();
    }

    public function getCode(bool $quoted = false):string
    {
        if ($this->isPk) {
            return '$this->' . $this->fluentParts['type'];
        }
        if ($this->isBuiltinType) {
            $parts = [$this->fluentParts['type'], $this->fluentParts['nullable'], $this->fluentParts['default'], $this->fluentParts['after']];
            array_unshift($parts, '$this');
            return implode('->', array_filter(array_map('trim', $parts), 'trim'));
        }
        if (!$this->rawParts['default']) {
            $default = '';
        } elseif ($this->isPostgres() && $this->isEnum()) {
            $default =
                $this->rawParts['default'] ? ' DEFAULT ' . self::escapeQuotes(trim($this->rawParts['default'])) : '';
        } else {
            $default = $this->rawParts['default'] ? ' DEFAULT ' . trim($this->rawParts['default']) : '';
        }

        $code = $this->rawParts['type'] . ' ' . $this->rawParts['nullable'] . $default;
        if ($this->rawParts['after']) {
            $code .= ' ' . $this->rawParts['after'];
        }

        if ($this->isMysql() && $this->isEnum()) {
            return $quoted ? '"' . str_replace("\'", "'", $code) . '"' : $code;
        }
        return $quoted ? "'" . $code . "'" : $code;
    }

    public function getAlterExpression(bool $addUsingExpression = false):string
    {
        if ($this->isEnum() && $this->isPostgres()) {
            return "'" . sprintf('enum_%1$s USING %1$s::enum_%1$s', $this->column->name) . "'";
        }
        if ($this->column->dbType === 'tsvector') {
            return "'" . $this->rawParts['type'] . "'";
        }
        if ($addUsingExpression && $this->isPostgres()) {
            return "'" . $this->rawParts['type'] . " ".$this->rawParts['nullable']
                .' USING "'.$this->column->name.'"::'.$this->typeWithoutSize($this->rawParts['type'])."'";
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
        return StringHelper::startsWith($this->column->dbType, 'enum');
    }

    public static function escapeQuotes(string $str):string
    {
        return str_replace(["'", '"', '$'], ["\\'", "\\'", '\$'], $str);
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
        $items = implode(", ", array_map('self::wrapQuotes', $enum));
        return self::escapeQuotes($items);
    }

    public static function mysqlEnumToString(array $enum):string
    {
        return implode(', ', array_map('self::wrapQuotes', $enum));
    }

    private function defaultValueJson(array $value):string
    {
        if ($this->alter === true) {
            return "'" . str_replace('"', '\"', Json::encode($value)). "'";
        }
        return "\\'" . new Expression(Json::encode($value)) . "\\'";
    }

    private function defaultValueArray(array $value):string
    {
        return "'{" . str_replace('"', "\"", trim(Json::encode($value), '[]')) . "}'";
    }

    private function resolve():void
    {
        $dbType = $this->typeWithoutSize(strtolower($this->column->dbType));
        $type = $this->column->type;
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
        if ($this->fromDb === true) {
            $this->isBuiltinType = isset((new ColumnSchemaBuilder(''))->categoryMap[$type]);
        } else {
            $this->isBuiltinType = isset((new ColumnSchemaBuilder(''))->categoryMap[$dbType]);
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
        } else {
            $this->fluentParts['type'] = $type . $fluentSize;
            $this->rawParts['type'] =
                $this->column->dbType . (strpos($this->column->dbType, '(') !== false ? '' : $rawSize);
        }

        if ($this->isMysql() || $this->isMariaDb()) { // for MySQL `AFTER` is supported for `ALTER table` queries and not supported for `CREATE table` queries
            if ($this->previousColumnName) {
                $this->fluentParts['after'] = 'after(\''.$this->previousColumnName.'\')';
                $this->rawParts['after'] = 'AFTER '.$this->previousColumnName;
            }
        }

        $this->resolveDefaultValue();
    }

    private function resolveEnumType():void
    {
        if ($this->isPostgres()) {
            $this->rawParts['type'] = 'enum_' . $this->column->name;
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
            $this->fluentParts['default'] = ($this->column->allowNull === true) ? 'defaultValue(null)' : '';
            $this->rawParts['default'] = ($this->column->allowNull === true) ? 'NULL' : '';
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
                if ($this->isPostgres()) {
                    $this->rawParts['default'] = ((bool)$value === true ? "'t'" : "'f'");
                } else {
                    $this->rawParts['default'] = ((bool)$value === true ? '1' : '0');
                }
                break;
            case 'object':
                if ($value instanceof JsonExpression) {
                    $this->fluentParts['default'] = "defaultValue('" . Json::encode($value->getValue()) . "')";
                    $this->rawParts['default'] = $this->defaultValueJson($value->getValue());
                } elseif ($value instanceof ArrayExpression) {
                    $this->fluentParts['default'] = "defaultValue('" . Json::encode($value->getValue()) . "')";
                    $this->rawParts['default'] = $this->defaultValueArray($value->getValue());
                } else {
                    $this->fluentParts['default'] = 'defaultExpression("' . self::escapeQuotes((string)$value) . '")';
                    $this->rawParts['default'] = self::escapeQuotes((string)$value);
                }
                break;
            case 'array':
                $this->fluentParts['default'] = "defaultValue('" . Json::encode($value) . "')";
                $this->rawParts['default'] = $this->isJson()
                    ? $this->defaultValueJson($value)
                    : $this->defaultValueArray($value);
                break;
            default:
                $isExpression = StringHelper::startsWith($value, 'CURRENT')
                    || StringHelper::startsWith($value, 'LOCAL')
                    || substr($value, -1, 1) === ')';
                if ($isExpression) {
                    $this->fluentParts['default'] = 'defaultExpression("' . self::escapeQuotes((string)$value) . '")';
                } else {
                    $this->fluentParts['default'] = $expectInteger
                        ? 'defaultValue(' . $value . ')' : 'defaultValue("' . self::escapeQuotes((string)$value) . '")';
                }
                $this->rawParts['default'] = $expectInteger ? $value : self::wrapQuotes($value);
                if ($this->isMysql() && $this->isEnum()) {
                    $this->rawParts['default'] = self::escapeQuotes($this->rawParts['default']);
                }
        }
    }

    private function isDefaultAllowed():bool
    {
        $type = strtolower($this->column->dbType);
        if ($type === 'tsvector') {
            return false;
        }
        return !($this->isMysql() && !$this->isMariaDb() && in_array($type, ['blob', 'geometry', 'text', 'json']));
    }

    private function typeWithoutSize(string $type):string
    {
        return preg_replace('~(.*)(\(\d+\))~', '$1', $type);
    }

    private function isPostgres():bool
    {
        return $this->dbSchema instanceof PgSqlSchema;
    }

    private function isMysql():bool
    {
        return $this->dbSchema instanceof MySqlSchema;
    }

    private function isMariaDb():bool
    {
        return strpos($this->dbSchema->getServerVersion(), 'MariaDB') !== false;
    }
}
