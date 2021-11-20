<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\generators\RestActionGenerator;
use cebe\yii2openapi\lib\items\RestAction;
use tests\TestCase;
use Yii;

class RestActionGeneratorTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     * @param string $schemaFile
     * @param string $modelNs
     * @param        $expected
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     * @throws \yii\base\InvalidConfigException
     */
    public function testGenerate(string $schemaFile, string $modelNs, $expected):void
    {
        $config = new Config([
            'openApiPath' => $schemaFile,
            'modelNamespace' => $modelNs,
        ]);
        $result = (new RestActionGenerator($config))->generate();
        foreach ($result as $i => $data) {
            //echo $expected[$i]->requestMethod . ' ' . $expected[$i]->urlPath . ' : ' . $expected[$i]->route . PHP_EOL;
            self::assertEquals($expected[$i], $data);
        }
    }

    /**
     * @dataProvider dataProviderWithNamingMap
     * @param string $schemaFile
     * @param string $modelNs
     * @param array  $namingMap
     * @param        $expected
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     * @throws \yii\base\InvalidConfigException
     */
    public function testGenerateWithNamingMap(string $schemaFile, string $modelNs, array $namingMap, $expected):void
    {
        $config = new Config([
            'openApiPath' => $schemaFile,
            'modelNamespace' => $modelNs,
            'controllerModelMap' => $namingMap,
        ]);
        $result = (new RestActionGenerator($config))->generate();
        foreach ($result as $i => $data) {
            //echo $expected[$i]->requestMethod . ' ' . $expected[$i]->urlPath . ' : ' . $expected[$i]->route . PHP_EOL;
            self::assertEquals($expected[$i], $data);
        }
    }

    /**
     * @dataProvider dataProviderWithUrlPrefixes
     * @param string $schemaFile
     * @param string $modelNs
     * @param array  $urlPrefixes
     * @param        $expected
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     * @throws \yii\base\InvalidConfigException
     */
    public function testGenerateWithUrlPrefixes(string $schemaFile, string $modelNs, array $urlPrefixes, $expected):void
    {
        $config = new Config([
            'openApiPath' => $schemaFile,
            'modelNamespace' => $modelNs,
            'urlPrefixes' => $urlPrefixes,
        ]);
        $result = (new RestActionGenerator($config))->generate();
        foreach ($result as $i => $data) {
            //echo $expected[$i]->requestMethod . ' ' . $expected[$i]->urlPath . ' : ' . $expected[$i]->route . PHP_EOL;
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
                        'prefix' => '',
                        'prefixSettings' => [],
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

    public function dataProviderWithUrlPrefixes():array
    {
        return [
            [
                '@specs/petstore_urlprefixes.yaml',
                'app\\mymodels',
                [
                    'animals' => '',
                    '/info' => [
                        'module' => 'pet-info',
                        'path' => '@app/modules/petinfo/controllers',
                        'namespace' => '\app\modules\petinfo\controllers',
                    ],
                    '/api/v1' => ['path' => '@app/modules/api/v1/controllers', 'namespace' => '\app\api\v1\controllers'],
                ],
                [
                    new RestAction([
                        'id' => 'list',
                        'urlPath' => '/api/v1/pets',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'api/v1/pets',
                        'controllerId' => 'pet',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'prefix' => '/api/v1',
                        'prefixSettings' => [
                            'path' => '@app/modules/api/v1/controllers',
                            'namespace' => '\app\api\v1\controllers',
                        ],
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'array'],
                    ]),
                    new RestAction([
                        'id' => 'create',
                        'urlPath' => '/api/v1/pets',
                        'requestMethod' => 'POST',
                        'urlPattern' => 'api/v1/pets',
                        'controllerId' => 'pet',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'prefix' => '/api/v1',
                        'prefixSettings' => [
                            'path' => '@app/modules/api/v1/controllers',
                            'namespace' => '\app\api\v1\controllers',
                        ],
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'view',
                        'controllerId' => 'pet',
                        'urlPath' => '/animals/pets/{id}',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'animals/pets/<id:[\w-]+>',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'string']],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'prefix' => 'animals',
                        'prefixSettings' => [],
                        'responseWrapper' => ['item' => '', 'list' => '', 'type' => 'object'],
                    ]),
                    new RestAction([
                        'id' => 'delete',
                        'urlPath' => '/animals/pets/{id}',
                        'requestMethod' => 'DELETE',
                        'urlPattern' => 'animals/pets/<id:[\w-]+>',
                        'controllerId' => 'pet',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'string']],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'prefix' => 'animals',
                        'prefixSettings' => [],
                        'responseWrapper' => null,
                    ]),
                    new RestAction([
                        'id' => 'update',
                        'urlPath' => '/animals/pets/{id}',
                        'requestMethod' => 'PATCH',
                        'urlPattern' => 'animals/pets/<id:[\w-]+>',
                        'controllerId' => 'pet',
                        'idParam' => 'id',
                        'params' => ['id' => ['type' => 'string']],
                        'modelName' => 'Pet',
                        'modelFqn' => 'app\mymodels\Pet',
                        'prefix' => 'animals',
                        'prefixSettings' => [],
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
                        'urlPath' => '/info/pet-details',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'info/pet-details',
                        'controllerId' => 'pet-detail',
                        'idParam' => null,
                        'params' => [],
                        'modelName' => null,
                        'modelFqn' => null,
                        'prefix' => '/info',
                        'prefixSettings' => [
                            'module' => 'pet-info',
                            'path' => '@app/modules/petinfo/controllers',
                            'namespace' => '\app\modules\petinfo\controllers',
                        ],
                        'responseWrapper' => null,
                    ]),
                ],
            ],
        ];
    }

    public function dataProviderWithNamingMap():array
    {
        return [
            [
                '@specs/blog_v2.yaml',
                'app\\models',
                ['Post' => 'BlogPost', 'Comment' => 'Reply'],
                [
                    new RestAction([
                        'id' => 'list',
                        'urlPath' => '/posts',
                        'requestMethod' => 'GET',
                        'urlPattern' => 'posts',
                        'controllerId' => 'blog-post',
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
                        'controllerId' => 'blog-post',
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
                        'controllerId' => 'blog-post',
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
                        'controllerId' => 'blog-post',
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
                        'controllerId' => 'reply',
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
                        'controllerId' => 'reply',
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
                        'controllerId' => 'blog-post',
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
                        'controllerId' => 'reply',
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
                        'controllerId' => 'reply',
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
                        'controllerId' => 'reply',
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
        ];
    }

    private function getOpenApiSchema(string $file)
    {
        $schemaFile = Yii::getAlias($file);
        return Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
    }
}
