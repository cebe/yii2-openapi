<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use cebe\yii2openapi\lib\SchemaToDatabase;
use tests\TestCase;
use Yii;
use yii\helpers\VarDumper;
use function array_keys;

class SchemaToDatabaseTest extends TestCase
{
    public function testFindJunctionSchemas()
    {
        $schemaFile = Yii::getAlias("@specs/many2many.yaml");
        $result = (new SchemaToDatabase(new Config(['openApiPath' => $schemaFile])))->findJunctionSchemas();
        //VarDumper::dump($result->indexByJunctionRef());
        //VarDumper::dump($result->indexByJunctionSchema());
        self::assertInstanceOf(JunctionSchemas::class, $result);
        self::assertEqualsCanonicalizing(
            ['junction_Photos2Posts', 'junction_PostsGallery', 'junction_PostsAttaches'],
            array_keys($result->indexByJunctionSchema())
        );
        self::assertEqualsCanonicalizing(
            ['Post', 'Photo'],
            array_keys($result->indexByClassSchema())
        );
        self::assertEquals('junction_PostAttaches', $result->addPrefix('PostAttaches'));
        self::assertEquals('PostAttaches', $result->trimPrefix('junction_PostAttaches'));
        self::assertTrue($result->isJunctionSchema('PostsAttaches'));
        self::assertTrue($result->isJunctionSchema('junction_PostsAttaches'));
        self::assertTrue($result->isManyToManyProperty('Post', 'image'));
        self::assertTrue($result->isManyToManyProperty('Post', 'photo'));
        self::assertTrue($result->isManyToManyProperty('Photo', 'article'));
        self::assertTrue($result->isManyToManyProperty('Photo', 'post'));
        self::assertTrue($result->isJunctionProperty('junction_PostsGallery', 'image'));
        self::assertTrue($result->isJunctionProperty('PostsGallery', 'article'));
        self::assertTrue($result->isJunctionProperty('junction_Photos2Posts', 'photo'));
        self::assertTrue($result->isJunctionProperty('junction_Photos2Posts', 'post'));
        self::assertTrue($result->isJunctionProperty('Photos2Posts', 'post'));
        self::assertTrue($result->isJunctionRef('Photo', 'posts_gallery'));
        self::assertTrue($result->isJunctionRef('Photo', 'posts_attaches'));
        self::assertTrue($result->isJunctionRef('Post', 'posts_gallery'));
    }

    public function testGenerateModels()
    {
        $schemaFile = Yii::getAlias("@specs/many2many.yaml");
        $converter = new SchemaToDatabase(new Config(['openApiPath' => $schemaFile]));
        $result = $converter->prepareModels();
        self::assertNotEmpty($result);
        self::assertArrayHasKey('Post', $result);
        self::assertArrayHasKey('Tag', $result);
        self::assertArrayHasKey('Photo', $result);
        self::assertArrayHasKey('Photos2Posts', $result);
        self::assertArrayHasKey('PostsGallery', $result);
        self::assertArrayHasKey('PostsAttaches', $result);
        self::assertInstanceOf(DbModel::class, $result['Photo']);
        self::assertInstanceOf(DbModel::class, $result['Post']);

        self::assertNotEmpty($result['Post']->many2many);
        self::assertArrayHasKey('tags', $result['Post']->many2many);
        self::assertArrayHasKey('photos', $result['Post']->many2many);
        self::assertArrayHasKey('images', $result['Post']->many2many);
        self::assertArrayHasKey('attaches', $result['Post']->many2many);
        self::assertArrayHasKey('posts_photos', $result['Post']->relations);
        self::assertArrayNotHasKey('posts_photos', $result['Post']->many2many);

        self::assertNotEmpty($result['Photo']->many2many);
        self::assertArrayNotHasKey('tags', $result['Photo']->many2many);
        self::assertArrayHasKey('posts', $result['Photo']->many2many);
        self::assertArrayHasKey('articles', $result['Photo']->many2many);
        self::assertArrayHasKey('targets', $result['Photo']->many2many);
        self::assertArrayHasKey('photos_posts', $result['Photo']->relations);
        self::assertArrayNotHasKey('posts_photos', $result['Post']->many2many);

        self::assertNotEmpty($result['PostsGallery']->attributes['image']);
        self::assertNotEmpty($result['PostsGallery']->attributes['article']);
        self::assertArrayHasKey('image', $result['PostsGallery']->relations);
        self::assertArrayHasKey('article', $result['PostsGallery']->relations);

        self::assertNotEmpty($result['PostsAttaches']->attributes['target']);
        self::assertNotEmpty($result['PostsAttaches']->attributes['attach']);
        self::assertArrayHasKey('target', $result['PostsAttaches']->relations);
        self::assertArrayHasKey('attach', $result['PostsAttaches']->relations);

        self::assertNotEmpty($result['Photos2Posts']->attributes['post']);
        self::assertNotEmpty($result['Photos2Posts']->attributes['photo']);
        self::assertArrayHasKey('post', $result['Photos2Posts']->relations);
        self::assertArrayHasKey('photo', $result['Photos2Posts']->relations);

        self::assertFalse($result['Post']->many2many['tags']->hasViaModel);
        self::assertFalse($result['Tag']->many2many['posts']->hasViaModel);
        self::assertTrue($result['Post']->many2many['photos']->hasViaModel);
        self::assertTrue($result['Photo']->many2many['posts']->hasViaModel);
    }
}
