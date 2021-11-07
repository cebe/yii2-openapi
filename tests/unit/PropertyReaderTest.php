<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\openapi\SchemaReader;
use tests\TestCase;
use Yii;

class PropertyReaderTest extends TestCase
{
    public function testPkProperty()
    {
        $schema = $this->getSchema();
        $prop = $schema->getProperty('uid');
        self::assertTrue($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertFalse($prop->isReference());
        self::assertEquals('uid', $prop->getName());
        self::assertEquals(null, $prop->guessDefault());
        self::assertEquals('string', $prop->guessPhpType());
        self::assertEquals('string', $prop->guessDbType());
        self::assertEquals([null, null], $prop->guessMinMax());
        self::assertEquals(128, $prop->getMaxLength());
        self::assertEquals(null, $prop->getMinLength());
        self::assertEquals(true, $prop->isReadonly());
    }

    public function testSimpleProperty()
    {
        $schema = $this->getSchema();
        $prop = $schema->getProperty('created_at');
        self::assertFalse($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertFalse($prop->isReference());
        self::assertEquals('created_at', $prop->getName());
        self::assertEquals(null, $prop->guessDefault());
        self::assertEquals('string', $prop->guessPhpType());
        self::assertEquals('date', $prop->guessDbType());
        self::assertEquals([null, null], $prop->guessMinMax());
        self::assertEquals(null, $prop->getMaxLength());
        self::assertEquals(null, $prop->getMinLength());
        self::assertEquals(false, $prop->isReadonly());
        self::assertFalse($prop->hasEnum());
    }

    public function testRefProperty()
    {
        $schema = $this->getSchema();
        $prop = $schema->getProperty('category');
        self::assertFalse($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertTrue($prop->isReference());
        self::assertTrue($prop->isRefPointerToSchema());
        self::assertFalse($prop->isRefPointerToSelf());
        self::assertFalse($prop->hasItems());
        self::assertFalse($prop->hasRefItems());
        self::assertEquals('category', $prop->getName());
        self::assertEquals('Category', $prop->getSchemaNameByReference());
        self::assertEquals('Category', $prop->getClassNameByReference());
        $ref = $prop->getRefSchema();
        self::assertInstanceOf(SchemaReader::class, $ref);
    }

    public function testRefItemsProperty()
    {

    }

    private function getSchema():SchemaReader
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        return new SchemaReader($openApi->components->schemas['Post']);
    }
}