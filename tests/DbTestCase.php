<?php

namespace tests;

use cebe\yii2openapi\generator\ApiGenerator;
use Yii;
use yii\di\Container;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

class DbTestCase extends \PHPUnit\Framework\TestCase
{
    protected function prepareTempDir()
    {
        FileHelper::removeDirectory(__DIR__ . '/tmp/docker_app');
        FileHelper::createDirectory(__DIR__ . '/tmp/docker_app');
        Yii::setAlias('@app', __DIR__ . '/tmp/docker_app');
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        $fileConfig = require __DIR__ . '/config/console.php';
        new $appClass(ArrayHelper::merge($fileConfig, $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
    }

    protected function setUp()
    {
        if (getenv('IN_DOCKER') !== 'docker') {
            $this->markTestSkipped('For docker env only');
        }
        $this->prepareTempDir();
        $this->mockApplication();
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (getenv('IN_DOCKER') === 'docker') {
            $this->destroyApplication();
        }
    }

    protected function runGenerator($configFile, string $dbName)
    {
        $config = require $configFile;
        $config['migrationPath'] = "@app/migrations_{$dbName}_db/";
        $generator = new ApiGenerator($config);
        self::assertTrue($generator->validate(), print_r($generator->getErrors(), true));
        $codeFiles = $generator->generate();
        foreach ($codeFiles as $file) {
            $file->save();
        }
    }
}
