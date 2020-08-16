<?php

namespace tests\unit;

use cebe\yii2openapi\lib\PathAutoCompletion;
use tests\TestCase;
use Yii;

class PathAutoCompletionTest extends TestCase
{

    public function testComplete()
    {
        Yii::setAlias('@vendor', __DIR__.'/items');
        $this->prepareTempDir();
        Yii::setAlias('@app', __DIR__.'/../specs');
        Yii::setAlias('@runtime', __DIR__.'/../tmp/app');


        $completion = (new PathAutoCompletion())->complete();
        self::assertNotEmpty($completion);
        self::assertArrayHasKey('openApiPath', $completion);
        self::assertArrayHasKey('controllerNamespace', $completion);
        self::assertArrayHasKey('fakerNamespace', $completion);
        self::assertArrayHasKey('modelNamespace', $completion);
        self::assertEquals($completion['modelNamespace'], $completion['fakerNamespace']);
        self::assertNotEquals($completion['modelNamespace'], $completion['openApiPath']);
        self::assertContains('@app/blog.yaml', $completion['openApiPath']);
        self::assertContains('@app/petstore.yaml', $completion['openApiPath']);
    }
}
