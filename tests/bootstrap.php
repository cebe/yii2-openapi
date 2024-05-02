<?php

error_reporting(-1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@specs', __DIR__ . '/specs');
Yii::setAlias('@fixtures', __DIR__ . '/fixtures');
Yii::setAlias('@tests', __DIR__);

if (YII_DEBUG) {
    function p($var = '', $vardump = false)
    {
        echo PHP_EOL."<pre>".PHP_EOL;
        !$vardump ? print_r($var) : var_dump($var);
        echo PHP_EOL."</pre>".PHP_EOL;
    }

    function pd($var = '', $vardump = false)
    {
        echo PHP_EOL."<pre>".PHP_EOL;
        !$vardump ? print_r($var) : var_dump($var);
        echo PHP_EOL."</pre>".PHP_EOL;
        die;
    }
}
