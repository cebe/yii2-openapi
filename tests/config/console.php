<?php

use cebe\yii2openapi\Bootstrap;

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
                dirname(__DIR__).'/tmp/docker_app/migrations', // TODO remove
                dirname(__DIR__).'/tmp/docker_app/migrations_mysql_db', // TODO remove
                dirname(__DIR__).'/tmp/docker_app/migrations_maria_db', // TODO remove
                dirname(__DIR__).'/tmp/docker_app/migrations_pgsql_db', // TODO remove
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

if (true) { // TODO remove this entire section
    // enable Gii module
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        'generators' => [
            // add ApiGenerator to Gii module
            'api' => \cebe\yii2openapi\generator\ApiGenerator::class,
        ],
    ];
}

return $config;
