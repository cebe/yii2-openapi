<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\SchemaToDatabase;
use tests\TestCase;
use Yii;

class SchemaToDatabaseTest extends TestCase
{
     public function testGenerateModels()
     {
         $schemaFile = Yii::getAlias("@specs/many2many.yaml");
         $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
         $converter = new SchemaToDatabase([]);
         $result = $converter->generateModels($openApi);
         self::assertNotEmpty($result);
         self::assertArrayHasKey('Post', $result);
         self::assertArrayHasKey('Tag', $result);
         self::assertArrayHasKey('Photo', $result);
         self::assertArrayHasKey('Photos2Posts', $result);
         self::assertInstanceOf(DbModel::class, $result['Post']);
         self::assertNotEmpty($result['Post']->many2many);
         self::assertArrayHasKey('tags', $result['Post']->many2many);
         self::assertArrayHasKey('photos', $result['Post']->many2many);
         self::assertArrayHasKey('photos2posts', $result['Post']->relations);
         self::assertArrayNotHasKey('photos2posts', $result['Post']->many2many);
         self::assertFalse($result['Post']->many2many['tags']->hasViaModel);
         self::assertFalse($result['Tag']->many2many['posts']->hasViaModel);
         self::assertTrue($result['Post']->many2many['photos']->hasViaModel);
         self::assertTrue($result['Photo']->many2many['posts']->hasViaModel);
     }
}
