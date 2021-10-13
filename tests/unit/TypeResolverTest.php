<?php

namespace tests\unit;

use cebe\yii2openapi\lib\SchemaTypeResolver;
use tests\TestCase;
use yii\db\Schema as YiiDbSchema;
use cebe\openapi\spec\Schema;

class TypeResolverTest extends TestCase
{
    /**
     * @dataProvider forDbTypeDataProvider
     * @param \cebe\openapi\spec\Schema $property
     * @param                           $isPrimary
     * @param                           $expected
     */
    public function testResolveDbType(Schema $property, $isPrimary, $expected):void
    {
        self::assertEquals($expected, SchemaTypeResolver::schemaToDbType($property, $isPrimary));
    }

    public function forDbTypeDataProvider():array
    {
        return [
            [new Schema(['type'=>'integer', 'format'=>'int64']), true, YiiDbSchema::TYPE_BIGPK],
            [new Schema(['type'=>'integer', 'format'=>'int32']), true, YiiDbSchema::TYPE_PK],
            [new Schema(['type'=>'boolean']), false, YiiDbSchema::TYPE_BOOLEAN],
            [new Schema(['type'=>'number', 'format'=>'float']), false, YiiDbSchema::TYPE_FLOAT],
            [new Schema(['type'=>'number', 'format'=>'double']), false, YiiDbSchema::TYPE_DOUBLE],
            [new Schema(['type'=>'integer', 'format'=>'date-time']), false, YiiDbSchema::TYPE_INTEGER],
            [new Schema(['type'=>'string']), false, YiiDbSchema::TYPE_TEXT],
            [new Schema(['type'=>'string', 'x-db-type'=>'varchar']), false, YiiDbSchema::TYPE_STRING],
            [new Schema(['type'=>'string', 'x-db-type'=>'JSON']), false, YiiDbSchema::TYPE_JSON],
            [new Schema(['type'=>'string', 'x-db-type'=>'tsvector']), false, 'tsvector'],
            [new Schema(['type'=>'string', 'enum'=>['a', 'b', 'c']]), false, 'string'],
            [new Schema(['type'=>'string', 'format'=>'email']), false, YiiDbSchema::TYPE_STRING],
            [new Schema(['type'=>'string', 'maxLength'=>100]), false, YiiDbSchema::TYPE_STRING],
            [new Schema(['type'=>'string', 'maxLength'=>10000]),  false, YiiDbSchema::TYPE_TEXT],
            [new Schema(['type'=>'string', 'format'=>'date-time']), false, YiiDbSchema::TYPE_DATETIME],
        ];
    }

    /**
     * @dataProvider forPhpTypeDataProvider
     * @param \cebe\openapi\spec\Schema $property
     * @param string                    $expected
     */
    public function testResolvePhpType(Schema $property, string $expected):void
    {
        self::assertEquals($expected, SchemaTypeResolver::schemaToPhpType($property));
    }

    public function forPhpTypeDataProvider():array
    {
        return [
            [new Schema(['type'=>'integer', 'format'=>'int64']), 'int'],
            [new Schema(['type'=>'integer']), 'int'],
            [new Schema(['type'=>'boolean']), 'bool'],
            [new Schema(['type'=>'number']), 'float'],
            [new Schema(['type'=>'number', 'format'=>'float']), 'float'],
            [new Schema(['type'=>'number', 'format'=>'double']), 'double'],
            [new Schema(['type'=>'string']), 'string'],
            [new Schema(['type'=>'string', 'format'=>'date']), 'string'],
            [new Schema(['type'=>'array']), 'array'],
            [new Schema(['type'=>'string', 'x-db-type'=>'json']), 'array'],
            [new Schema(['type'=>'integer', 'x-db-type'=>'int[]']), 'array'],
        ];
    }

}
