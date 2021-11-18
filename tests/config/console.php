<?php

use cebe\yii2openapi\Bootstrap;

return [
    'id' => 'cebe/yii2-openapi',
    'timeZone' => 'UTC',
    'basePath' => dirname(__DIR__) . '/tmp/docker_app',
    'runtimePath' => dirname(__DIR__) . '/tmp',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'bootstrap'=>[Bootstrap::class],
    'controllerMap' => [
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => dirname(__DIR__).'/migrations',
        ],
    ],
    'components' => [
        'pgsql' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'pgsql:host=postgres;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'mysql' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=mysql;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'maria' => [
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=maria;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
        'db'=>[
            'class' => \yii\db\Connection::class,
            'dsn' => 'mysql:host=mysql;dbname=testdb',
            'username' => 'dbuser',
            'password' => 'dbpass',
            'charset' => 'utf8',
            'tablePrefix'=>'itt_',
        ],
    ],
];
