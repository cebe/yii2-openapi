<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

class CustomSpecAttr
{
    // --- For component schema ---
    //Custom table name
    public const TABLE = 'x-table';
    //Primary key property name, if it is different from "id" (Only one value, compound keys not supported yet)
    public const PRIMARY_KEY = 'x-pk';
    //List of table indexes
    public const INDEXES = 'x-indexes';

    // --- For each property schema ---
    //Custom fake data for property
    public const FAKER = 'x-faker';
    // Custom db type (MUST CONTAINS ONLY DB TYPE! (json, jsonb, uuid, varchar etc))
    public const DB_TYPE = 'x-db-type';
    /**
     * Provide default value by database expression
     * @example `current_timestamp()`
     * @see https://dev.mysql.com/doc/refman/8.0/en/data-type-defaults.html
     * @see https://github.com/cebe/yii2-openapi/blob/master/README.md#x-db-default-expression
     */
    public const DB_DEFAULT_EXPRESSION = 'x-db-default-expression';

    /**
     * Foreign key constraints. See README for usage docs
     */
    public const FK_ON_DELETE = 'x-fk-on-delete';
    public const FK_ON_UPDATE = 'x-fk-on-update';

    /**
     * Foreign key column name. See README for usage docs
     */
    public const FK_COLUMN_NAME = 'x-fk-column-name';
}
