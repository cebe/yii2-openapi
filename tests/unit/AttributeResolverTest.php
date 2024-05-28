<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Schema;
use cebe\yii2openapi\lib\AttributeResolver;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\JunctionSchemas;
use cebe\yii2openapi\lib\items\ManyToManyRelation;
use cebe\yii2openapi\lib\openapi\ComponentSchema;
use tests\DbTestCase;
use Yii;
use yii\helpers\VarDumper;
use const PHP_EOL;

class AttributeResolverTest extends DbTestCase
{
    public function testManyToManyResolve()
    {
        $schemaFile = Yii::getAlias("@specs/many2many.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = new ComponentSchema($openApi->components->schemas['Post'], 'Post');
        $postModel = (new AttributeResolver('Post', $schema, new JunctionSchemas([])))->resolve();
        self::assertNotEmpty($postModel->many2many);
        $relation = $postModel->many2many['tags'];
        self::assertInstanceOf(ManyToManyRelation::class, $relation);
        self::assertEquals('Tag', $relation->relatedClassName);
        self::assertEquals('Post', $relation->className);
        self::assertEquals('id', $relation->pkAttribute->propertyName);
        self::assertEquals('posts2tags', $relation->getViaTableName());
        self::assertEquals(['id' => 'tag_id'], $relation->getLink());
        self::assertEquals(['post_id' => 'id'], $relation->getViaLink());

        $schema = new ComponentSchema($openApi->components->schemas['Tag'], 'Tag');
        $tagModel = (new AttributeResolver('Tag', $schema, new JunctionSchemas([])))->resolve();
        self::assertNotEmpty($tagModel->many2many);
        $relation = $tagModel->many2many['posts'];
        self::assertInstanceOf(ManyToManyRelation::class, $relation);
        self::assertEquals('Post', $relation->relatedClassName);
        self::assertEquals('Tag', $relation->className);
        self::assertEquals('id', $relation->pkAttribute->propertyName);
        self::assertEquals('posts2tags', $relation->getViaTableName());
        self::assertEquals(['id' => 'post_id'], $relation->getLink());
        self::assertEquals(['tag_id' => 'id'], $relation->getViaLink());


    }

    /**
     * @dataProvider dataProvider
     * @param string                              $schemaName
     * @param \cebe\openapi\spec\Schema           $openApiSchema
     * @param \cebe\yii2openapi\lib\items\DbModel $expected
     */
    public function testResolve(string $schemaName, Schema $openApiSchema, DbModel $expected):void
    {
        $schema = new ComponentSchema($openApiSchema, $schemaName);
        $resolver = new AttributeResolver($schemaName, $schema, new JunctionSchemas([]));
        $model = $resolver->resolve();
        //echo $schemaName . PHP_EOL;
        self::assertEquals($expected->name, $model->name);
        self::assertEquals($expected->tableName, $model->tableName);
        self::assertEquals($expected->description, $model->description);
        self::assertEquals($expected->tableAlias, $model->tableAlias);
        //VarDumper::dump($model->indexes);
        self::assertEquals($expected->indexes, $model->indexes);
        foreach ($model->relations as $name => $relation) {
            self::assertTrue(isset($expected->relations[$name]));
            self::assertEquals($expected->relations[$name], $relation);
        }
        foreach ($model->attributes as $name => $attribute) {
            self::assertTrue(isset($expected->attributes[$name]));
            self::assertEquals($expected->attributes[$name], $attribute);
        }
    }

    public function dataProvider():array
    {
        $schemaFile = Yii::getAlias("@specs/blog.yaml");
        $fixture = require Yii::getAlias('@fixtures/blog.php');
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        return [
            [
                'User',
                $openApi->components->schemas['User'],
                $fixture['user'],
            ],
            [
                'Category',
                $openApi->components->schemas['Category'],
                $fixture['category'],
            ],
            [
                'Post',
                $openApi->components->schemas['Post'],
                $fixture['post'],
            ],
            [
                'Comment',
                $openApi->components->schemas['Comment'],
                $fixture['comment'],
            ],
        ];
    }

    public function testResolveRefNoObject()
    {
        $schemaFile = Yii::getAlias("@specs/ref_noobject.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = new ComponentSchema($openApi->components->schemas['PersonWatch'], 'PersonWatch');
        $resolver = new AttributeResolver('PersonWatch', $schema, new JunctionSchemas([]));
        $model = $resolver->resolve();
        $fixture = require Yii::getAlias('@fixtures/non-db.php');
        $testModel = $fixture['personWatch'];
        self::assertEquals($testModel, $model);
    }

    public function testResolveNonDbModel()
    {
        $schemaFile = Yii::getAlias("@specs/petstore_jsonapi.yaml");
        $openApi = Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
        $schema = new ComponentSchema($openApi->components->schemas['PetStatistic'], 'PetStatistic');
        $resolver = new AttributeResolver('PetStatistic', $schema, new JunctionSchemas([]));
        $model = $resolver->resolve();
        $fixture = require Yii::getAlias('@fixtures/non-db.php');
        $testModel = $fixture['PetStatistic'];
        self::assertEquals($testModel, $model);
    }
}
