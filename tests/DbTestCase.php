<?php

namespace tests;

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
}