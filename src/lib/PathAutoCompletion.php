<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Throwable;
use Yii;
use yii\helpers\FileHelper;

class PathAutoCompletion
{
    public function complete():array
    {
        $vendor = Yii::getAlias('@vendor');
        $app = Yii::getAlias('@app');
        $runtime = Yii::getAlias('@runtime');
        $paths = [];
        $pathIterator = new RecursiveDirectoryIterator($app);
        $recursiveIterator = new RecursiveIteratorIterator($pathIterator);
        $files = new RegexIterator($recursiveIterator, '~.+\.(json|yaml|yml)$~i', RegexIterator::GET_MATCH);
        foreach ($files as $file) {
            if (strpos($file[0], $vendor) === 0) {
                $file = FileHelper::normalizePath('@vendor' . substr($file[0], strlen($vendor)));
            } elseif (strpos($file[0], $runtime) === 0) {
                $file = null;
            } elseif (strpos($file[0], $app) === 0) {
                $file = FileHelper::normalizePath('@app' . substr($file[0], strlen($app)));
            } else {
                $file = $file[0];
            }

            if ($file !== null) {
                $paths[] = $file;
            }
        }

        $namespaces = array_merge(...array_map([$this, 'completeAlias'], array_keys(Yii::$aliases)));

        return [
            'openApiPath' => $paths,
            'controllerNamespace' => $namespaces,
            'modelNamespace' => $namespaces,
            'fakerNamespace' => $namespaces,
            'migrationNamespace' => $namespaces,
            'transformerNamespace' => $namespaces,
//            'urlConfigFile' => [
//                '@app/config/urls.rest.php',
//            ],
        ];
    }

    private function completeAlias(string $alias):array
    {
        $path = Yii::getAlias($alias, false);
        if (in_array($alias, ['@web', '@runtime', '@vendor', '@bower', '@npm'])) {
            return [];
        }
        if (!file_exists($path)) {
            return [];
        }
        try {
            $dirs =  FileHelper::findDirectories($path, ['except' => ['vendor/','runtime/','assets/','.git/','.svn/']]);
        } catch (Throwable $e) {
            // ignore errors with file permissions
            Yii::error($e);
            return [];
        }
        return array_map(static function ($dir) use ($path, $alias) {
            return str_replace('/', '\\', substr($alias, 1) . substr($dir, strlen($path)));
        }, $dirs);
    }
}
