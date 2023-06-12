<?php

namespace tests\unit;

use cebe\yii2openapi\lib\PathAutoCompletion;
use cebe\yii2openapi\lib\Config;
use tests\TestCase;
use Yii;

class PathAutoCompletionTest extends TestCase
{
    public function testComplete()
    {
        $this->registerApp();

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

    public function testCompletionFromConfigAndDefault()
    {
        $this->registerApp();

        $completion = (new PathAutoCompletion(new Config([
            'openApiPath' => '@root/openapi/schema.yaml',
            'controllerNamespace' => 'api\\controllers',
        ])))->complete();

        self::assertNotEmpty($completion);
        self::assertArrayHasKey('openApiPath', $completion);
        self::assertSame(['@root/openapi/schema.yaml'], $completion['openApiPath']);
        self::assertSame(['api\\controllers'], $completion['controllerNamespace']);
        self::assertSame(['app\\models'], $completion['modelNamespace']);
        self::assertContains('yii\messages\sl', $completion['migrationNamespace']);
    }

    private function registerApp()
    {
        Yii::setAlias('@vendor', __DIR__.'/items');
        $this->prepareTempDir();
        Yii::setAlias('@runtime', __DIR__.'/../tmp/app');

        $this->mockRealApplication(); // to register cache component
        Yii::setAlias('@app', __DIR__.'/../specs');
        Yii::setAlias('@webroot', __DIR__.'@app/web');
    }
}
