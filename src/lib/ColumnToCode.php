<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use yii\db\ArrayExpression;
use yii\db\ColumnSchema;
use yii\db\ColumnSchemaBuilder;
use yii\db\JsonExpression;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\db\Schema;
use yii\helpers\StringHelper;
use function array_key_exists;
use function in_array;
use function is_array;
use function method_exists;
use function strpos;
use function strtolower;
use function substr;
use function trim;
use function ucfirst;
use const PHP_EOL;

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
    private $columnUnique;

    /**
     * @var bool
     */
    private $fromDb;

    /**
     * @var bool
     */
    private $typeOnly = false;
    private $defaultOnly = false;

    /**
     * ColumnToCode constructor.
     * @param \yii\db\Schema       $dbSchema
     * @param \yii\db\ColumnSchema $column
     * @param bool                 $columnUnique (Pass unique marker from schema, because ColumnSchema not contain it)
     * @param bool                 $fromDb (if from database we prefer column type for usage, from schema - dbType)
     */
    public function __construct(Schema $dbSchema, ColumnSchema $column, bool $columnUnique, bool $fromDb = false)
    {
        $this->dbSchema = $dbSchema;
        $this->column = $column;
        $this->columnUnique = $columnUnique;
        $this->fromDb = $fromDb;
    }

    public function resolveTypeOnly():string
    {
        $this->typeOnly = true;
        return $this->resolve();
    }

    public function resolveDefaultOnly():string
    {
        $this->defaultOnly = true;
        return $this->resolve();
    }

    public function resolve():string
    {
        $dbType = $this->column->dbType;
        $type = $this->column->type;
        //Primary Keys
        if (array_key_exists($type, self::PK_TYPE_MAP)) {
            return '$this->' . self::PK_TYPE_MAP[$type];
        }
        if (array_key_exists($dbType, self::PK_TYPE_MAP)) {
            return '$this->' . self::PK_TYPE_MAP[$dbType];
        }
        if ($this->fromDb === true) {
            $categoryType = (new ColumnSchemaBuilder(''))->categoryMap[$type] ?? '';
        } else {
            $categoryType = (new ColumnSchemaBuilder(''))->categoryMap[$dbType] ?? '';
        }

        $columnTypeMethod = 'resolve' . ucfirst($categoryType) . 'Type';

        if (StringHelper::startsWith($dbType, 'enum')) {
            $columnTypeMethod = 'resolveEnumType';
        }
        if (StringHelper::startsWith($dbType, 'set')) {
            $columnTypeMethod = 'resolveSetType';
        }
        if (StringHelper::startsWith($dbType, 'tsvector')) {
            $columnTypeMethod = 'resolveTsvectorType';
        }
        if (isset($column->dimension) && $column->dimension > 0) {
            $columnTypeMethod = 'resolveArrayType';
        }

        if (method_exists($this, $columnTypeMethod)) {
            return $this->$columnTypeMethod();
        }

        return $categoryType && !$this->defaultOnly? $this->resolveCommon() : $this->resolveRaw();
    }

    private function buildRawDefaultValue():string
    {
        $value = $this->column->defaultValue;
        $nullable = $this->column->allowNull;
        $isJson = in_array($this->column->dbType, ['json', 'jsonb']);
        if ($value === null) {
            return $nullable === true ? 'DEFAULT NULL' : '';
        }
        switch (gettype($value)) {
            case 'integer':
                return 'DEFAULT ' . $value;
            case 'object':
                if ($value instanceof JsonExpression) {
                    return 'DEFAULT '.self::defaultValueJson($value->getValue());
                }
                if ($value instanceof ArrayExpression) {
                    return 'DEFAULT '.self::defaultValueArray($value->getValue());
                }
                return 'DEFAULT ' .(string) $value;
            case 'double':
                // ensure type cast always has . as decimal separator in all locales
                return 'DEFAULT ' . str_replace(',', '.', (string)$value);
            case 'boolean':
                return 'DEFAULT ' . ($value ? 'TRUE' : 'FALSE');
            case 'array':
                return $isJson? 'DEFAULT '.self::defaultValueJson($value)
                                :'DEFAULT '.self::defaultValueArray($value);
            default:
                if (stripos($value, 'NULL::') !== false) {
                    return 'DEFAULT NULL';
                }
                return 'DEFAULT '.self::wrapQuotes($value);
        }
    }

    private static function defaultValueJson(array $value):string
    {
        return "'".\json_encode($value)."'";
    }
    private static function defaultValueArray(array $value):string
    {
        return "'{".trim(\json_encode($value), '[]')."}'";
    }
    public static function escapeQuotes(string $str):string
    {
        return str_replace(["'", '"', '$'], ["\\'", "\\'", '\$'], $str);
    }

    public static function wrapQuotesOnlyRaw(string $code, bool $escapeQuotes = false):string
    {
        if (strpos($code, '$this->') === false) {
            return $escapeQuotes ? '"' . self::escapeQuotes($code) . '"' : '"' . $code . '"';
        }
        return $code;
    }

    public static function wrapQuotes(string $str, string $quotes = "'", bool $escape = true):string
    {
        if ($escape && strpos($str, $quotes) !== false) {
            return $quotes . self::escapeQuotes($str) . $quotes;
        }
        return $quotes . $str . $quotes;
    }

    public static function enumToString(array $enum): string
    {
        $items = implode(",", array_map(function ($v) {
            return self::wrapQuotes($v);
        }, $enum));
        return self::escapeQuotes($items);
    }

    private function resolveCommon():string
    {
        $size = $this->column->size ? '(' . $this->column->size . ')' : '()';
        $default = $this->buildDefaultValue();
        $nullable = $this->column->allowNull === true ? 'null()' : 'notNull()';
        if (array_key_exists($this->column->dbType, self::INT_TYPE_MAP)) {
            $type = self::INT_TYPE_MAP[$this->column->dbType] . $size;
        } elseif (array_key_exists($this->column->type, self::INT_TYPE_MAP)) {
            $type = self::INT_TYPE_MAP[$this->column->type] . $size;
        } else {
            $type = $this->column->type . $size;
        }
        return $this->buildString($type, $default, $nullable);
    }

    private function resolveRaw():string
    {
        $nullable = $this->column->allowNull ? 'NULL' : 'NOT NULL';
        $type = $this->column->dbType;
        $default = $this->isDefaultAllowed() ? $this->buildRawDefaultValue(): '';
        if ($this->defaultOnly) {
            return $default;
        }

        $size = $this->column->size ? '(' . $this->column->size . ')' : '';
        $type = strpos($type, '(') === false ? $type . $size : $type;
        if ($this->typeOnly === true) {
            return $type;
        }
        $columns = $nullable . ($default ? ' ' . trim($default) : '');
        return $type . ' ' . $columns;
    }

    private function resolveEnumType():string
    {
        if (!$this->column->enumValues || !is_array($this->column->enumValues)) {
            return '';
        }
        $default = $this->buildRawDefaultValue();
        if ($this->defaultOnly) {
            return $default;
        }
        $nullable = $this->column->allowNull ? 'NULL' : 'NOT NULL';
        if ($this->isPostgres()) {
            $type = 'enum_' . $this->column->name;
        } else {
            $values = array_map(
                function ($v) {
                    return self::wrapQuotes($v);
                },
                $this->column->enumValues
            );
            $type = "enum(" . implode(', ', $values) . ")";
        }
        if ($this->typeOnly === true) {
            return $type;
        }
        $columns = $nullable . ($default ? ' ' . trim($default) : '');
        return $type . ' ' . $columns;
    }

    private function resolveSetType():string
    {
        $default = $this->buildRawDefaultValue();
        if ($this->defaultOnly) {
            return $default;
        }
        $type = $this->column->dbType;
        $nullable = $this->column->allowNull ? 'NULL' : 'NOT NULL';
        $columns = $nullable . ($default ? ' ' . trim($default) : '');
        return $type . ' ' . $columns;
    }

    private function resolveArrayType():string
    {
        $default = $this->buildDefaultValue();
        if ($this->defaultOnly) {
            return $default;
        }
        $nullable = $this->column->allowNull === true ? 'null()' : 'notNull()';
        $type = $this->column->dbType;
        return $this->buildString($type, $default, $nullable);
    }

    private function resolveTsvectorType():string
    {
        //\var_dump($this->column);
        return $this->resolveRaw();
    }

    private function buildDefaultValue():string
    {
        $value = $this->column->defaultValue;
        if (!$this->isDefaultAllowed()) {
            return '';
        }
        if ($value === null) {
            return ($this->column->allowNull === true)? 'defaultValue(null)' : '';
        }

        switch (gettype($value)) {
            case 'integer':
                return 'defaultValue(' . (int)$value . ')';
            case 'double':
            case 'float':
                // ensure type cast always has . as decimal separator in all locales
                return 'defaultValue("' . str_replace(',', '.', (string)$value) . '")';
            case 'boolean':
                return $value === true ? 'defaultValue(true)' : 'defaultValue(false)';
            case 'object':
                if ($value instanceof JsonExpression) {
                    return 'defaultValue(' . json_encode($value->getValue()) . ')';
                }
                return 'defaultExpression("' . self::escapeQuotes((string)$value) . '")';
            case 'array':
                return (string)'defaultValue(' . json_encode($value) . ')';
            default:
            {
                if (stripos($value, 'NULL::') !== false) {
                    return '';
                }
                if (
                    StringHelper::startsWith($value, 'CURRENT')
                    || StringHelper::startsWith($value, 'LOCAL')
                    || substr($value, -1, 1) === ')') {
                    //TIMESTAMP MARKER OR DATABASE FUNCTION
                    return 'defaultExpression("' . self::escapeQuotes((string)$value) . '")';
                }
                return 'defaultValue("' . self::escapeQuotes((string)$value) . '")';
            }
        }
    }

    private function buildString(string $type, $default = '', $nullable = ''):string
    {
        if ($this->typeOnly === true) {
            $columnParts = [$type];
        } else {
            $columnParts = [$type, $nullable, $default];
            if ($this->columnUnique) {
                $columnParts[] = 'unique()';
            }
        }
        array_unshift($columnParts, '$this');
        return implode('->', array_filter(array_map('trim', $columnParts), 'trim'));
    }

    private function isDefaultAllowed():bool
    {
        $type = strtolower($this->column->dbType);
        if ($this->dbSchema instanceof MySqlSchema && in_array($type, ['blob', 'geometry', 'text', 'json'])) {
            //Only mysql specific restriction, mariadb can it
            return strpos($this->dbSchema->getServerVersion(), 'MariaDB') !== false;
        }
        return true;
    }

    private function isPostgres():bool
    {
        return $this->dbSchema instanceof PgSqlSchema;
    }
}
