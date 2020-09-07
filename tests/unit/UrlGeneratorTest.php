<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\items\RouteData;
use cebe\yii2openapi\lib\UrlGenerator;
use tests\TestCase;
use Yii;

class UrlGeneratorTest extends TestCase
{

    /**
     * @dataProvider dataProvider
    */
    public function testGenerate(string $schemaFile, string $modelNs, $expected)
    {
        $openApi = $this->getOpenApiSchema($schemaFile);
        $result = (new UrlGenerator($openApi, $modelNs))->generate();
        foreach ($result as $i => $data){
            self::assertEquals($expected[$i], $data);
        }
    }

    private function getOpenApiSchema(string $file)
    {
        $schemaFile = Yii::getAlias($file);
        return Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
    }

    public function dataProvider(): array {
        return [
            [
                '@specs/petstore_xtable.yaml',
                'app\\mymodels',
                [
                    new RouteData([
                        'path'=>'/pets',
                        'method'=>'GET',
                        'pattern'=>'pets',
                        'controllerId'=>'pet',
                        'actionId'=>'list',
                        'idParam'=>null,
                        'actionParams'=>[],
                        'modelClass'=>'app\mymodels\Pet',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'array']
                    ]),
                    new RouteData([
                        'path'=>'/pets',
                        'method'=>'POST',
                        'pattern'=>'pets',
                        'controllerId'=>'pet',
                        'actionId'=>'create',
                        'idParam'=>null,
                        'actionParams'=>[],
                        'modelClass'=>'app\mymodels\Pet',
                        'responseWrapper'=>null
                    ]),
                    new RouteData([
                        'path'=>'/pets/{id}',
                        'method'=>'GET',
                        'pattern'=>'pets/<id:[\w-]+>',
                        'controllerId'=>'pet',
                        'actionId'=>'view',
                        'idParam'=>'id',
                        'actionParams'=>['id'=>['type'=>'string']],
                        'modelClass'=>'app\mymodels\Pet',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'object']
                    ]),
                    new RouteData([
                        'path'=>'/pets/{id}',
                        'method'=>'DELETE',
                        'pattern'=>'pets/<id:[\w-]+>',
                        'controllerId'=>'pet',
                        'actionId'=>'delete',
                        'idParam'=>'id',
                        'actionParams'=>['id'=>['type'=>'string']],
                        'modelClass'=>'app\mymodels\Pet',
                        'responseWrapper'=>null
                    ]),
                    new RouteData([
                        'path'=>'/pets/{id}',
                        'method'=>'PATCH',
                        'pattern'=>'pets/<id:[\w-]+>',
                        'controllerId'=>'pet',
                        'actionId'=>'update',
                        'idParam'=>'id',
                        'actionParams'=>['id'=>['type'=>'string']],
                        'modelClass'=>'app\mymodels\Pet',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'object']
                    ]),
                    new RouteData([
                        'path'=>'/petComments',
                        'method'=>'GET',
                        'pattern'=>'petComments',
                        'controllerId'=>'pet-comment',
                        'actionId'=>'list',
                        'idParam'=>null,
                        'actionParams'=>[],
                        'modelClass'=>null,
                        'responseWrapper'=>null
                    ]),
                    new RouteData([
                        'path'=>'/pet-details',
                        'method'=>'GET',
                        'pattern'=>'pet-details',
                        'controllerId'=>'pet-detail',
                        'actionId'=>'list',
                        'idParam'=>null,
                        'actionParams'=>[],
                        'modelClass'=>null,
                        'responseWrapper'=>null
                    ]),
                ]
            ],
            [
                '@specs/blog_v2.yaml',
                'app\\models',
                [
                    new RouteData([
                        'path'=>'/posts',
                        'method'=>'GET',
                        'pattern'=>'posts',
                        'controllerId'=>'post',
                        'actionId'=>'list',
                        'idParam'=>null,
                        'actionParams'=>[],
                        'modelClass'=>'app\models\Post',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'array']
                    ]),
                    new RouteData([
                        'path'=>'/posts/{id}',
                        'method'=>'GET',
                        'pattern'=>'posts/<id:\d+>',
                        'controllerId'=>'post',
                        'actionId'=>'view',
                        'idParam'=>'id',
                        'actionParams'=>['id'=>['type'=>'integer']],
                        'modelClass'=>'app\models\Post',
                        'responseWrapper'=>['item'=>'post', 'list'=>null, 'type'=>'object']
                    ]),
                    new RouteData([
                        'path'=>'/posts/{postId}/comments',
                        'method'=>'GET',
                        'pattern'=>'posts/<postId:\d+>/comments',
                        'controllerId'=>'comment',
                        'actionId'=>'list-for-post',
                        'idParam'=>'postId',
                        'actionParams'=>['postId'=>['type'=>'integer']],
                        'modelClass'=>'app\models\Comment',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'array']
                    ]),
                    new RouteData([
                        'path'=>'/posts/{postId}/comments',
                        'method'=>'POST',
                        'pattern'=>'posts/<postId:\d+>/comments',
                        'controllerId'=>'comment',
                        'actionId'=>'create-for-post',
                        'idParam'=>'postId',
                        'actionParams'=>['postId'=>['type'=>'integer']],
                        'modelClass'=>'app\models\Comment',
                        'responseWrapper'=>null
                    ]),
                    new RouteData([
                        'path'=>'/posts/{postSlug}/comment/{id}',
                        'method'=>'GET',
                        'pattern'=>'posts/<postSlug:[\w-]+>/comment/<id:\d+>',
                        'controllerId'=>'comment',
                        'actionId'=>'view-for-post',
                        'idParam'=>'id',
                        'actionParams'=>[
                            'postSlug'=>['type'=>'string'],
                            'id'=>['type'=>'integer']
                        ],
                        'modelClass'=>'app\models\Comment',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'object']
                    ]),
                    new RouteData([
                        'path'=>'/posts/{postSlug}/comment/{id}',
                        'method'=>'DELETE',
                        'pattern'=>'posts/<postSlug:[\w-]+>/comment/<id:\d+>',
                        'controllerId'=>'comment',
                        'actionId'=>'delete-for-post',
                        'idParam'=>'id',
                        'actionParams'=>[
                            'postSlug'=>['type'=>'string'],
                            'id'=>['type'=>'integer']
                        ],
                        'modelClass'=>'app\models\Comment',
                        'responseWrapper'=>null
                    ]),
                    new RouteData([
                        'path'=>'/posts/{postSlug}/comment/{id}',
                        'method'=>'PATCH',
                        'pattern'=>'posts/<postSlug:[\w-]+>/comment/<id:\d+>',
                        'controllerId'=>'comment',
                        'actionId'=>'update-for-post',
                        'idParam'=>'id',
                        'actionParams'=>[
                            'postSlug'=>['type'=>'string'],
                            'id'=>['type'=>'integer']
                        ],
                        'modelClass'=>'app\models\Comment',
                        'responseWrapper'=>['item'=>'', 'list'=>'', 'type'=>'object']
                    ]),
                ]
            ],
        ];
    }
}
