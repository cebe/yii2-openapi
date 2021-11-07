<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\openapi\PropertyReader;
use cebe\yii2openapi\lib\openapi\SchemaReader;
use tests\TestCase;
use Yii;

class SchemaReaderTest extends TestCase
{
    public function testWithoutReference()
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = new SchemaReader($openApi->components->schemas['User']);
        self::assertFalse($schema->isReference());
        self::assertTrue($schema->isObjectSchema());
        self::assertTrue($schema->hasProperties());
        self::assertEquals('id', $schema->getPkName());
        self::assertEquals(['id', 'username', 'email', 'password'], $schema->getRequiredProperties());
        self::assertEquals('users', $schema->getTableName('User'));
        self::assertTrue($schema->hasProperty('email'));
        self::assertInstanceOf(PropertyReader::class, $schema->getProperty('username'));
        foreach ($schema->getProperties() as $prop) {
            self::assertInstanceOf(PropertyReader::class, $prop);
        }
    }

    public function testByReference()
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = new SchemaReader($openApi->components->schemas['Post']->properties['category']);
        self::assertTrue($schema->isObjectSchema());
        self::assertEquals('id', $schema->getPkName());
        self::assertTrue($schema->isReference());
        self::assertEquals('categories', $schema->getTableName('Category'));
        self::assertTrue($schema->hasProperties());
        self::assertTrue($schema->hasProperty('title'));
    }
}