<?php

namespace tests\unit;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Reference;
use cebe\yii2openapi\lib\openapi\ResponseSchema;
use tests\TestCase;
use Yii;

class ResponseSchemaTest extends TestCase
{
    /**
     * @dataProvider dataProviderForGuessModel
     */
    public function testGuessModel(Operation $operation, array $expected)
    {
        self::assertEquals(
            $expected['expected'],
            ResponseSchema::guessModelClass($operation, $expected['actionName'])
        );
    }

    /**
     * @dataProvider dataProviderForFindResponseWrapper
     */
    public function testFindResponseWrapper(Operation $operation, array $expected)
    {
        self::assertEquals(
            $expected['expected'],
            ResponseSchema::findResponseWrapper($operation, $expected['modelClass'])
        );
    }

    /**
     * @dataProvider dataProviderForGuessResponseRelations
     */
    public function testGuessResponseRelations(Operation $operation, array $expected)
    {
        $result = ResponseSchema::guessResponseRelations($operation);
        self::assertEquals($expected['expected'], $result);
    }

    public function dataProviderForGuessResponseRelations()
    {
        $openApi = $this->getOpenApiSchema('@specs/blog_jsonapi.yaml');
        $expects = [
            '/me' => [
                'get' => [
                    'expected' => [],
                ],
            ],
            '/auth/new-password' => [
                'get' => [
                    'expected' => [],
                ],
            ],
            '/categories' => [
                'get' => [
                    'expected' => ['posts'],
                ],
            ],
            '/posts/{id}' => [
                'get' => [
                    'expected' => ['author', 'category', 'comments'],
                ],
            ],
            '/posts' => [
                'get' => [
                    'expected' => ['author', 'category', 'comments'],
                ],
            ],
            '/posts/{id}/relationships/author' => [
                'get' => [
                    'expected' => [],
                ],
            ],
            '/post/{postId}/comments/{id}' => [
                'get' => [
                    'expected' => ['user', 'post'],
                ],
            ]
        ];
        yield from $this->filterPaths($openApi, $expects);
    }

    public function dataProviderForFindResponseWrapper()
    {
        $openApi = $this->getOpenApiSchema('@specs/blog_v2.yaml');
        $expects = [
            '/posts/{id}/relationships/comments' => [
                'get' => [
                    'modelClass' => 'Comment',
                    'expected' => ['item' => '', 'list' => '', 'type' => 'array'],
                ],
            ],
            '/posts/{postId}/comments' => [
                'get' => [
                    'modelClass' => 'Comment',
                    'expected' => ['item' => '', 'list' => '', 'type' => 'array'],
                ],
                'post' => [
                    'modelClass' => 'Comment',
                    'expected' => null,
                ],
            ],
            '/posts/{slug}/comment/{id}' => [
                'get' => [
                    'modelClass' => 'Comment',
                    'expected' => ['item' => '', 'list' => '', 'type' => 'object'],
                ],
                'patch' => [
                    'modelClass' => 'Comment',
                    'expected' => ['item' => '', 'list' => '', 'type' => 'object'],
                ],
            ],
            '/posts' => [
                'get' => [
                    'modelClass' => 'Post',
                    'expected' => ['item' => '', 'list' => '', 'type' => 'array'],
                ],
            ],
            '/posts/{id}' => [
                'get' => [
                    'modelClass' => 'Post',
                    'expected' => ['item' => 'post', 'list' => null, 'type' => 'object'],
                ],
            ],
        ];
        yield from $this->filterPaths($openApi, $expects);
    }

    public function dataProviderForGuessModel()
    {
        $openApi = $this->getOpenApiSchema('@specs/blog_v2.yaml');
        $expects = [
            '/posts' => [
                'get' => [
                    'actionName' => 'list',
                    'expected' => 'Post',
                ],
            ],
            '/posts/{id}' => [
                'get' => [
                    'actionName' => 'foo',
                    'expected' => null,
                ],
            ],
        ];
        yield from $this->filterPaths($openApi, $expects);
    }

    private function filterPaths(OpenApi $openApi, array $expects)
    {
        foreach ($openApi->paths as $path => $pathItem) {
            if (!isset($expects[$path])) {
                continue;
            }
            if ($pathItem instanceof Reference) {
                $pathItem = $pathItem->resolve();
            }
            foreach ($pathItem->getOperations() as $method => $operation) {
                if (!isset($expects[$path][$method])) {
                    continue;
                }
                yield [$operation, $expects[$path][$method]];
            }
        }
    }

    private function getOpenApiSchema(string $file)
    {
        $schemaFile = Yii::getAlias($file);
        return Reader::readFromYamlFile($schemaFile, OpenApi::class, false);
    }
}
