<?php

namespace cebe\yii2openapi\lib;

class CustomSpecAttr
{
    // --- For component schema ---
    //Custom table name
    public const TABLE = 'x-table';
    //Primary key property name, if it different from "id" (Only one value, compound keys not supported yet)
    public const PRIMARY_KEY = 'x-pk';

    // --- For each property schema ---
    //Custom fake data for property
    public const FAKER = 'x-faker';
    // Custom db type (MUST CONTAINS ONLY DB TYPE! (json, jsonb, uuid, varchar etc))
    public const DB_TYPE = 'x-db-type';
    // Flag for database unique constraint
    public const UNIQUE = 'x-db-unique';
}