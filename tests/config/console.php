<?php

use cebe\yii2openapi\Bootstrap;
use cebe\yii2openapi\generator\ApiGenerator;

$config = [
    'id' => 'cebe/yii2-openapi',
    'timeZone' => 'UTC',
    'basePath' => dirname(__DIR__) . '/tmp/docker_app',
    'runtimePath' => dirname(__DIR__) . '/tmp',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                dirname(__DIR__).'/migrations',
            ],
        ],
        // see usage instructions at https://www.yiiframework.com/doc/guide/2.0/en/db-migrations#separated-migrations
        'migrate-mysql' => [ // just for development of tests
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                dirname(__DIR__).'/migrations',
                dirname(__DIR__).'/tmp/docker_app/migrations',
                dirname(__DIR__).'/tmp/docker_app/migrations_mysql_db',
            ],
        ],
        'migrate-maria' => [ // just for development of tests
            'class' => \yii\console\controllers\MigrateController::class,
            'db' => 'maria',
            'migrationPath' => [
                dirname(__DIR__).'/tmp/docker_app/migrations_maria_db',
            ],
        ],
        'migrate-pgsql' => [ // just for development of tests
            'class' => \yii\console\controllers\MigrateController::class,
            'db' => 'pgsql',
            'migrationPath' => [
                dirname(__DIR__).'/tmp/docker_app/migrations_pgsql_db',
            ],
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
            'schemaMap' => [
                'mysql' => \SamIT\Yii2\MariaDb\Schema::class
            ]
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

return $config;
