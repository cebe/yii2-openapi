<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Schema;
use cebe\yii2openapi\lib\AttributeResolver;
use cebe\yii2openapi\lib\items\DbModel;
use tests\TestCase;
use Yii;
use const PHP_EOL;

class AttributeResolverTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param string                              $schemaName
     * @param \cebe\openapi\spec\Schema           $schema
     * @param \cebe\yii2openapi\lib\items\DbModel $expected
     */
    public function testResolve(string $schemaName, Schema $schema, DbModel $expected):void
    {
        $resolver = new AttributeResolver($schemaName, $schema);
        $model = $resolver->resolve();
        echo $schemaName.PHP_EOL;
        self::assertEquals($expected->name, $model->name);
        self::assertEquals($expected->tableName, $model->tableName);
        self::assertEquals($expected->description, $model->description);
        self::assertEquals($expected->tableAlias, $model->tableAlias);
        foreach ($model->relations as $name => $relation){
            self::assertTrue(isset($expected->relations[$name]));
            self::assertEquals($expected->relations[$name], $relation);
        }
        foreach ($model->attributes as $name => $attribute){
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
                $fixture['user']
            ],
            [
                'Category',
                $openApi->components->schemas['Category'],
                $fixture['category']
            ],
            [
                'Post',
                $openApi->components->schemas['Post'],
                $fixture['post']
            ],
            [
                'Comment',
                $openApi->components->schemas['Comment'],
                $fixture['comment']
            ],
        ];
    }
}