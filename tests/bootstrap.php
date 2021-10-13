<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@specs', __DIR__ . '/specs');
Yii::setAlias('@fixtures', __DIR__ . '/fixtures');
Yii::setAlias('@tests', __DIR__);
