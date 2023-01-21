<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\db;

class ColumnSchema extends \yii\db\ColumnSchema
{
    /**
     * @var string|null|false
     * Custom DB type which contains real DB type
     * Contains x-db-type string if present in OpenAPI YAML/json file
     * @see \cebe\yii2openapi\lib\items\Attribute::$xDbType and `x-db-type` docs in README.md
     * Used to detect what kind of migration code for column is to be generated
     * e.g. `double_p double precision NULL DEFAULT NULL`
     * instead of
     * ```php
     *   $this->createTable('{{%alldbdatatypes}}', [
     *       ...
     *       'double_p' => 'double precision NULL DEFAULT NULL',
     *       ...
     * ```
     */
    public $xDbType;

    /**
     * @var ?string
     * Provide default value by database expression
     * @example `current_timestamp()`
     * @see https://dev.mysql.com/doc/refman/8.0/en/data-type-defaults.html
     * @see https://github.com/cebe/yii2-openapi/blob/master/README.md#x-db-default-expression
     */
    // public $xDbDefaultExpression;
}
