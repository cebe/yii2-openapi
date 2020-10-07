<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\items\RestAction;
use cebe\yii2openapi\lib\UrlGenerator;
use tests\TestCase;
use Yii;
use const PHP_EOL;

class UrlGeneratorTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testGenerate(string $schemaFile, string $modelNs, $expected)
    {
        $openApi = $this->getOpenApiSchema($schemaFile);
        $result = (new UrlGenerator($openApi, $modelNs))->generate();
        foreach ($result as $i => $data) {
            echo $expected[$i]->requestMethod . ' ' . $expected[$i]->urlPath . ' : ' . $expected[$i]->route . PHP_EOL;
            self::assertEquals($expected[$i], $data);
        }
    }

    public function dataProvider():array
    {
        return [
            [
                '@specs/blog_v2.yaml',
                'app\\models',
                [
                    new RestAction([
                        'id' => 'list',
                        'urlPath' => '/posts',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts',
                        'controllerId' => 'post',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => 'Post',
                        'modelFqn' => 'app\models\Post',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'array'],
                    ]),
                    new RestAction([
                        'id' => 'view',
                        'urlPath' => '/posts/{id}',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts/<id:\d+>',
                        'controllerId' => 'post',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'integer']],
                        'modelName' => 'Post',
                        'modelFqn' => 'app\models\Post',
                        'responseWrapper' => ['item' => 'post', 'list' => null, 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'view-related-category',
                        'urlPath' => '/posts/{id}/relationships/category',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts/<id:\d+>/relationships/category',
                        'controllerId' => 'post',
                        'idParam' => 'id',
                        'params' => [
                            'id' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Post',
                        'modelFqn' => 'app\models\Post',
                        'responseWrapper' => ['item' => 'category', 'list' => null, 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'list-related-comments',
                        'urlPath' => '/posts/{id}/relationships/comments',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts/<id:\d+>/relationships/comments',
                        'controllerId' => 'post',
                        'idParam' => 'id',
                        'params' => [
                            'id' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Post',
                        'modelFqn' => 'app\models\Post',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'array'],
                    ]),
                    new RestAction([
                        'id' => 'list-for-post',
                        'urlPath' => '/posts/{postId}/comments',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts/<postId:\d+>/comments',
                        'controllerId' => 'comment',
                        'idParam' => 'postId',
                        'params' => [
                            'postId' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Comment',
                        'modelFqn' => 'app\models\Comment',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'array'],
                    ]),
                    new RestAction([
                        'id' => 'create-for-post',
                        'urlPath' => '/posts/{postId}/comments',
                        'requestMethod' => 'POST',
                        'urlPattern' => 'posts/<postId:\d+>/comments',
                        'controllerId' => 'comment',
                        'idParam' => 'postId',
                        'params' => [
                            'postId' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Comment',
                        'modelFqn' => 'app\models\Comment',
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'view-for-category',
                        'urlPath' => '/category/{categoryId}/posts/{id}',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'category/<categoryId:\d+>/posts/<id:\d+>',
                        'controllerId' => 'post',
                        'idParam' => 'id',
                        'params' => [
                            'categoryId' => ['type' => 'integer'],
                            'id' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Post',
                        'modelFqn' => 'app\models\Post',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'view-for-post',
                        'urlPath' => '/posts/{slug}/comment/{id}',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts/<slug:[\w-]+>/comment/<id:\d+>',
                        'controllerId' => 'comment',
                        'idParam' => 'id',
                        'params' => [
                            'slug' => ['type' => 'string'],
                            'id' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Comment',
                        'modelFqn' => 'app\models\Comment',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'delete-for-post',
                        'urlPath' => '/posts/{slug}/comment/{id}',
                        'requestMethod' => 'DELETE',
                        'urlPattern' => 'posts/<slug:[\w-]+>/comment/<id:\d+>',
                        'controllerId' => 'comment',
                        'idParam' => 'id',
                        'params' => [
                            'slug' => ['type' => 'string'],
                            'id' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Comment',
                        'modelFqn' => 'app\models\Comment',
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'update-for-post',
                        'urlPath' => '/posts/{slug}/comment/{id}',
                        'requestMethod' => 'PATCH',
                        'urlPattern' => 'posts/<slug:[\w-]+>/comment/<id:\d+>',
                        'controllerId' => 'comment',
                        'idParam' => 'id',
                        'params' => [
                            'slug' => ['type' => 'string'],
                            'id' => ['type' => 'integer'],
                        ],
                        'modelName' => 'Comment',
                        'modelFqn' => 'app\models\Comment',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'object'],
                    ]),
                ],
            ],
            [
                '@specs/petstore_xtable.yaml',
                'app\\mymodels',
                [
                    new RestAction([
                        'id' => 'list',
                        'urlPath' => '/pets',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'pets',
                        'controllerId' => 'pet',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'array'],
                    ]),
                    new RestAction([
                        'id' => 'create',
                        'urlPath' => '/pets',
                        'requestMethod' => 'POST',
                        'urlPattern' => 'pets',
                        'controllerId' => 'pet',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'view',
                        'controllerId' => 'pet',
                        'urlPath' => '/pets/{id}',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'pets/<id:[\w-]+>',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'string']],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'delete',
                        'urlPath' => '/pets/{id}',
                        'requestMethod' => 'DELETE',
                        'urlPattern' => 'pets/<id:[\w-]+>',
                        'controllerId' => 'pet',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'string']],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'update',
                        'urlPath' => '/pets/{id}',
                        'requestMethod' => 'PATCH',
                        'urlPattern' => 'pets/<id:[\w-]+>',
                        'controllerId' => 'pet',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'string']],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'list',
                        'urlPath' => '/petComments',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'petComments',
                        'controllerId' => 'pet-comment',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => null,
                        'modelFqn' => null,
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'list',
                        'urlPath' => '/pet-details',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'pet-details',
                        'controllerId' => 'pet-detail',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => null,
                        'modelFqn' => null,
                        'responseWrapper' => null,
                    ]),
                ],
            ],
        ];
    }

    private function getOpenApiSchema(string $file)
    {
        $schemaFile = Yii::getAlias($file);
        return Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
    }
}
