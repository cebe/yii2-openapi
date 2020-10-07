<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\FractalGenerator;
use cebe\yii2openapi\lib\items\FractalAction;
use cebe\yii2openapi\lib\items\RouteData;
use tests\TestCase;
use Yii;
use const PHP_EOL;

class FractalGeneratorTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     */
    public function testGenerate(string $schemaFile, string $modelNs, string $transformerNs, $expected)
    {
        $openApi = $this->getOpenApiSchema($schemaFile);
        $result = (new FractalGenerator($openApi, $modelNs, $transformerNs))->generate();
        foreach ($result as $i => $data) {
            echo $expected[$i]->requestMethod . ' ' . $expected[$i]->urlPath . ' : ' . $expected[$i]->route . PHP_EOL;
            self::assertEquals($expected[$i], $data);
        }
    }

    public function dataProvider():array
    {
        return [
            [
                '@specs/blog_jsonapi.yaml',
                'app\\models',
                'app\\transformers',
                $this->blogActions(),
            ],
            [
                '@specs/petstore_xtable.yaml',
                'app\\mymodels',
                'app\\mytransformers',
                $this->petStoreActions(),
            ],
        ];
    }

    private function getOpenApiSchema(string $file)
    {
        $schemaFile = Yii::getAlias($file);
        return Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
    }

    private function blogActions(): array {
        return [
            new FractalAction([
                'id' => 'view',
                'type' => RouteData::TYPE_PROFILE,
                'urlPath' => '/me',
                'requestMethod' => 'GET',
                'urlPattern' => 'me',
                'controllerId' => 'me',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'User',
                'modelFqn' => 'app\models\User',
                'transformerFqn'=>'app\transformers\UserTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'view-password-recovery',
                'type' => RouteData::TYPE_DEFAULT,
                'urlPath' => '/auth/password/recovery',
                'requestMethod' => 'GET',
                'urlPattern' => 'auth/password/recovery',
                'controllerId' => 'auth',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => null,
                'modelFqn' => null,
                'transformerFqn'=>null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'create-password-recovery',
                'type' => RouteData::TYPE_DEFAULT,
                'urlPath' => '/auth/password/recovery',
                'requestMethod' => 'POST',
                'urlPattern' => 'auth/password/recovery',
                'controllerId' => 'auth',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => null,
                'modelFqn' => null,
                'transformerFqn'=>null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'view-password-confirm-recovery',
                'type' => RouteData::TYPE_DEFAULT,
                'urlPath' => '/auth/password/confirm-recovery/{token}',
                'requestMethod' => 'GET',
                'urlPattern' => 'auth/password/confirm-recovery/<token:[\w-]+>',
                'controllerId' => 'auth',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [
                    'token'=> ['type'=>'string']
                ],
                'modelName' => null,
                'modelFqn' => null,
                'transformerFqn'=>null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'create-new-password',
                'type' => RouteData::TYPE_DEFAULT,
                'urlPath' => '/auth/new-password',
                'requestMethod' => 'POST',
                'urlPattern' => 'auth/new-password',
                'controllerId' => 'auth',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => null,
                'modelFqn' => null,
                'transformerFqn'=>null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'list',
                'type' => RouteData::TYPE_COLLECTION,
                'urlPath' => '/categories',
                'requestMethod' => 'GET',
                'urlPattern' => 'categories',
                'controllerId' => 'category',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'Category',
                'modelFqn' => 'app\\models\\Category',
                'transformerFqn'=>'app\\transformers\\CategoryTransformer',
                'expectedRelations' => ['posts'],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'create',
                'type' => RouteData::TYPE_COLLECTION,
                'urlPath' => '/categories',
                'requestMethod' => 'POST',
                'urlPattern' => 'categories',
                'controllerId' => 'category',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'Category',
                'modelFqn' => 'app\\models\\Category',
                'transformerFqn'=>'app\\transformers\\CategoryTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'list-for-category',
                'type' => RouteData::TYPE_COLLECTION_FOR,
                'urlPath' => '/categories/{categoryId}/posts',
                'requestMethod' => 'GET',
                'urlPattern' => 'categories/<categoryId:\d+>/posts',
                'controllerId' => 'post',
                'idParam' => null,
                'parentIdParam' => 'categoryId',
                'params' => [
                    'categoryId'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => ['author', 'category', 'comments'],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'create-for-category',
                'type' => RouteData::TYPE_COLLECTION_FOR,
                'urlPath' => '/categories/{categoryId}/posts',
                'requestMethod' => 'POST',
                'urlPattern' => 'categories/<categoryId:\d+>/posts',
                'controllerId' => 'post',
                'idParam' => null,
                'parentIdParam' => 'categoryId',
                'params' => [
                    'categoryId'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'list',
                'type' => RouteData::TYPE_COLLECTION,
                'urlPath' => '/posts',
                'requestMethod' => 'GET',
                'urlPattern' => 'posts',
                'controllerId' => 'post',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => ['author', 'category', 'comments'],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'create',
                'type' => RouteData::TYPE_COLLECTION,
                'urlPath' => '/posts',
                'requestMethod' => 'POST',
                'urlPattern' => 'posts',
                'controllerId' => 'post',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'view',
                'type' => RouteData::TYPE_RESOURCE,
                'urlPath' => '/posts/{id}',
                'requestMethod' => 'GET',
                'urlPattern' => 'posts/<id:\d+>',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => ['author', 'category', 'comments'],
                'relatedModel' => null
            ]),

            new FractalAction([
                'id' => 'delete',
                'type' => RouteData::TYPE_RESOURCE,
                'urlPath' => '/posts/{id}',
                'requestMethod' => 'DELETE',
                'urlPattern' => 'posts/<id:\d+>',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'update',
                'type' => RouteData::TYPE_RESOURCE,
                'urlPath' => '/posts/{id}',
                'requestMethod' => 'PATCH',
                'urlPattern' => 'posts/<id:\d+>',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\PostTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'update-upload-cover',
                'type' => RouteData::TYPE_RESOURCE_OPERATION,
                'urlPath' => '/posts/{id}/upload/cover',
                'requestMethod' => 'PUT',
                'urlPattern' => 'posts/<id:\d+>/upload/cover',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'view-related-author',
                'type' => RouteData::TYPE_RELATIONSHIP,
                'urlPath' => '/posts/{id}/relationships/author',
                'requestMethod' => 'GET',
                'urlPattern' => 'posts/<id:\d+>/relationships/author',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\UserTransformer',
                'expectedRelations' => [],
                'relatedModel' => 'User'
            ]),
            new FractalAction([
                'id' => 'view-for-post',
                'type' => RouteData::TYPE_RESOURCE_FOR,
                'urlPath' => '/post/{postId}/comments/{id}',
                'requestMethod' => 'GET',
                'urlPattern' => 'post/<postId:\d+>/comments/<id:\d+>',
                'controllerId' => 'comment',
                'idParam' => 'id',
                'parentIdParam' => 'postId',
                'params' => [
                    'id'=>['type'=>'integer'],
                    'postId'=>['type'=>'integer'],
                ],
                'modelName' => 'Comment',
                'modelFqn' => 'app\\models\\Comment',
                'transformerFqn'=>'app\\transformers\\CommentTransformer',
                'expectedRelations' => ['user', 'post'],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'list-related-comments',
                'type' => RouteData::TYPE_RELATIONSHIP,
                'urlPath' => '/posts/{id}/relationships/comments',
                'requestMethod' => 'GET',
                'urlPattern' => 'posts/<id:\d+>/relationships/comments',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\CommentTransformer',
                'expectedRelations' =>  ['user', 'post'],
                'relatedModel' => 'Comment'
            ]),
            new FractalAction([
                'id' => 'list-related-tags',
                'type' => RouteData::TYPE_RELATIONSHIP,
                'urlPath' => '/posts/{id}/relationships/tags',
                'requestMethod' => 'GET',
                'urlPattern' => 'posts/<id:\d+>/relationships/tags',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\TagTransformer',
                'expectedRelations' => [],
                'relatedModel' => 'Tag'
            ]),
            new FractalAction([
                'id' => 'update-related-tags',
                'type' => RouteData::TYPE_RELATIONSHIP,
                'urlPath' => '/posts/{id}/relationships/tags',
                'requestMethod' => 'PATCH',
                'urlPattern' => 'posts/<id:\d+>/relationships/tags',
                'controllerId' => 'post',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => [
                    'id'=>['type'=>'integer']
                ],
                'modelName' => 'Post',
                'modelFqn' => 'app\\models\\Post',
                'transformerFqn'=>'app\\transformers\\TagTransformer',
                'expectedRelations' => [],
                'relatedModel' => 'Tag'
            ]),
        ];
    }

    private function petStoreActions(): array
    {
        return [
            new FractalAction([
                'id' => 'list',
                'type'=>RouteData::TYPE_COLLECTION,
                'urlPath' => '/pets',
                'requestMethod' => 'GET',
                'urlPattern' => 'pets',
                'controllerId' => 'pet',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'Pet',
                'modelFqn' => 'app\mymodels\Pet',
                'transformerFqn'=>'app\mytransformers\PetTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'create',
                'type'=>RouteData::TYPE_COLLECTION,
                'urlPath' => '/pets',
                'requestMethod' => 'POST',
                'urlPattern' => 'pets',
                'controllerId' => 'pet',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => 'Pet',
                'modelFqn' => 'app\mymodels\Pet',
                'transformerFqn'=>'app\mytransformers\PetTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'view',
                'type'=>RouteData::TYPE_RESOURCE,
                'controllerId' => 'pet',
                'urlPath' => '/pets/{id}',
                'requestMethod' => 'GET',
                'urlPattern' => 'pets/<id:[\w-]+>',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => ['id' => ['type' => 'string']],
                'modelName' => 'Pet',
                'modelFqn' => 'app\mymodels\Pet',
                'transformerFqn'=>'app\mytransformers\PetTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'delete',
                'type'=>RouteData::TYPE_RESOURCE,
                'urlPath' => '/pets/{id}',
                'requestMethod' => 'DELETE',
                'urlPattern' => 'pets/<id:[\w-]+>',
                'controllerId' => 'pet',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => ['id' => ['type' => 'string']],
                'modelName' => 'Pet',
                'modelFqn' => 'app\mymodels\Pet',
                'transformerFqn'=>'app\mytransformers\PetTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'update',
                'type'=>RouteData::TYPE_RESOURCE,
                'urlPath' => '/pets/{id}',
                'requestMethod' => 'PATCH',
                'urlPattern' => 'pets/<id:[\w-]+>',
                'controllerId' => 'pet',
                'idParam' => 'id',
                'parentIdParam' => null,
                'params' => ['id' => ['type' => 'string']],
                'modelName' => 'Pet',
                'modelFqn' => 'app\mymodels\Pet',
                'transformerFqn'=>'app\mytransformers\PetTransformer',
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'list',
                'type'=>RouteData::TYPE_COLLECTION,
                'urlPath' => '/petComments',
                'requestMethod' => 'GET',
                'urlPattern' => 'petComments',
                'controllerId' => 'pet-comment',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => null,
                'modelFqn' => null,
                'transformerFqn'=> null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
            new FractalAction([
                'id' => 'list',
                'type'=>RouteData::TYPE_COLLECTION,
                'urlPath' => '/pet-details',
                'requestMethod' => 'GET',
                'urlPattern' => 'pet-details',
                'controllerId' => 'pet-detail',
                'idParam' => null,
                'parentIdParam' => null,
                'params' => [],
                'modelName' => null,
                'modelFqn' => null,
                'transformerFqn'=> null,
                'expectedRelations' => [],
                'relatedModel' => null
            ]),
        ];
    }
}
