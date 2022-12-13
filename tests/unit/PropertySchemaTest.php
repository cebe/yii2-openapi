<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\openapi\PropertySchema;
use cebe\yii2openapi\lib\openapi\ComponentSchema;
use tests\DbTestCase;
use Yii;

class PropertySchemaTest extends DbTestCase
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
        self::assertEquals('varchar', $prop->guessDbType());
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
    }

    public function testRefProperty()
    {
        $schema = $this->getSchema();
        $prop = $schema->getProperty('category');
        self::assertFalse($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertEquals('category', $prop->getName());
        self::assertTrue($prop->isReference());
        self::assertEquals('Category', $prop->getRefSchemaName());
        self::assertEquals('Category', $prop->getRefClassName());
        self::assertTrue($prop->isRefPointerToSchema());
        self::assertFalse($prop->isRefPointerToSelf());
        self::assertFalse($prop->hasItems());
        self::assertFalse($prop->hasRefItems());

        $refSchema = $prop->getRefSchema();
        self::assertInstanceOf(ComponentSchema::class, $refSchema);
        self::assertTrue($refSchema->hasProperties());
        $fkProperty = $prop->getTargetProperty();
        self::assertInstanceOf(PropertySchema::class, $fkProperty);
        self::assertEquals('id', $fkProperty->getName());
        self::assertTrue($fkProperty->isPrimaryKey());
        self::assertTrue($fkProperty->isReadonly());
        self::assertFalse($fkProperty->isRefPointerToSchema());
        self::assertFalse($fkProperty->isRefPointerToSelf());
        self::assertFalse($fkProperty->isReference());
    }

    public function testRefItemsProperty()
    {
        $schema = $this->getSchema();
        $prop = $schema->getProperty('comments');
        self::assertFalse($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertEquals('comments', $prop->getName());
        self::assertFalse($prop->isReference());
        self::assertEquals('Comment', $prop->getRefSchemaName());
        self::assertEquals('Comment', $prop->getRefClassName());
        self::assertTrue($prop->isRefPointerToSchema());
        self::assertFalse($prop->isRefPointerToSelf());
        self::assertTrue($prop->hasItems());
        self::assertTrue($prop->hasRefItems());

        $refSchema = $prop->getRefSchema();
        self::assertInstanceOf(ComponentSchema::class, $refSchema);
        self::assertTrue($refSchema->hasProperties());

        $fkProperty = $prop->getTargetProperty();
        self::assertInstanceOf(PropertySchema::class, $fkProperty);
        self::assertEquals('id', $fkProperty->getName());
        self::assertTrue($fkProperty->isPrimaryKey());
        self::assertTrue($fkProperty->isReadonly());
        self::assertFalse($fkProperty->isRefPointerToSchema());
        self::assertFalse($fkProperty->isRefPointerToSelf());
        self::assertFalse($fkProperty->isReference());
    }

    public function testSelfReferencedProperty()
    {
        $schemaFile = Yii::getAlias("@specs/menu.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = new ComponentSchema($openApi->components->schemas['Menu'], 'Menu');

        $prop = $schema->getProperty('parent');
        self::assertFalse($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertEquals('parent', $prop->getName());
        self::assertTrue($prop->isReference());
        self::assertEquals('Menu', $prop->getRefSchemaName());
        self::assertEquals('Menu', $prop->getRefClassName());
        self::assertTrue($prop->isRefPointerToSchema());
        self::assertTrue($prop->isRefPointerToSelf());
        self::assertFalse($prop->hasItems());
        self::assertFalse($prop->hasRefItems());

        $refSchema = $prop->getRefSchema();
        self::assertInstanceOf(ComponentSchema::class, $refSchema);
        self::assertTrue($refSchema->hasProperties());
        self::assertEquals($refSchema, $schema);
        self::assertEquals('id', $prop->getRefSchema()->getPkName());
        $fkProperty = $prop->getTargetProperty();
        self::assertInstanceOf(PropertySchema::class, $fkProperty);
        self::assertEquals('id', $fkProperty->getName());

        $prop = $schema->getProperty('childes');
        self::assertFalse($prop->isPrimaryKey());
        self::assertFalse($prop->isVirtual());
        self::assertEquals('childes', $prop->getName());
        self::assertFalse($prop->isReference());
        self::assertEquals('Menu', $prop->getRefSchemaName());
        self::assertEquals('Menu', $prop->getRefClassName());
        self::assertTrue($prop->isRefPointerToSchema());
        self::assertTrue($prop->isRefPointerToSelf());
        self::assertTrue($prop->hasItems());
        self::assertTrue($prop->hasRefItems());

        $refSchema = $prop->getRefSchema();
        self::assertInstanceOf(ComponentSchema::class, $refSchema);
        self::assertEquals($refSchema, $schema);
        $fkChildProperty = $prop->getTargetProperty();
        self::assertInstanceOf(PropertySchema::class, $fkProperty);
        self::assertEquals('id', $fkProperty->getName());
        self::assertEquals($fkChildProperty, $fkProperty);
    }

    private function getSchema():ComponentSchema
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        return new ComponentSchema($openApi->components->schemas['Post'], 'Post');
    }
}
