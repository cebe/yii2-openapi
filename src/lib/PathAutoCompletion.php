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
use cebe\yii2openapi\lib\Config;
use Yii;
use yii\helpers\FileHelper;

class PathAutoCompletion
{
    /**
     * @var ?Config
     */
    private $_config;

    public function __construct(?Config $config = null)
    {
        $this->_config = $config;
    }

    public function complete():array
    {
        return [
            'openApiPath' => $this->computePaths($this->_config),
            'controllerNamespace' => $this->computeNamesapces('controllerNamespace'),
            'modelNamespace' => $this->computeNamesapces('modelNamespace'),
            'fakerNamespace' => $this->computeNamesapces('fakerNamespace'),
            'migrationNamespace' => $this->computeNamesapces('migrationNamespace'),
            'transformerNamespace' => $this->computeNamesapces('transformerNamespace'),
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
            $dirs =  FileHelper::findDirectories($path, ['except' => ['vendor/','runtime/','assets/','.git/','.svn/', '/web']]);
        } catch (Throwable $e) {
            // ignore errors with file permissions
            Yii::error($e);
            return [];
        }
        return array_map(static function ($dir) use ($path, $alias) {
            return str_replace('/', '\\', substr($alias, 1) . substr($dir, strlen($path)));
        }, $dirs);
    }

    private function computeNamesapces(string $property): array
    {
        $config = $this->_config;
        if ($config && $config->$property) {
            return [$config->$property];
        }

        $key = 'cebe-yii2-openapi-autocompletion-data-namespaces';
        $list = Yii::$app->cache->get($key);
        if ($list !== false) {
            return $list;
        }
        $list = array_merge(...array_map([$this, 'completeAlias'], array_keys(Yii::$aliases)));
        Yii::$app->cache->set($key, $list, 3*24*60*60); // 3 days
        return $list;
    }

    private function computePaths(): array
    {
        $config = $this->_config;

        // First priority will be given to values present in config (example) to be shown in form fields.
        // Second to default values present in class cebe\yii2openapi\generator\ApiGenerator
        // Third will be given to values produced by PathAutoCompletion class

        if ($config && $config->openApiPath) {
            return [$config->openApiPath];
        }

        // check it is present in cache
        $key = 'cebe-yii2-openapi-autocompletion-data-paths';
        // use cache
        $list = Yii::$app->cache->get($key);
        if ($list !== false) {
            return $list;
        }

        // 3rd priority
        $vendor = Yii::getAlias('@vendor');
        $webroot = Yii::getAlias('@webroot');
        $tests = Yii::getAlias('@app/tests');
        $app = Yii::getAlias('@app');
        $runtime = Yii::getAlias('@runtime');
        $paths = [];
        $pathIterator = new RecursiveDirectoryIterator($app);
        $recursiveIterator = new RecursiveIteratorIterator($pathIterator);
        $files = new RegexIterator($recursiveIterator, '~.+\.(json|yaml|yml)$~i', RegexIterator::GET_MATCH);
        foreach ($files as $file) {
            if (strpos($file[0], $vendor) === 0) {
                $file = null;
            } elseif (strpos($file[0], $tests) === 0) {
                $file = null;
            } elseif (strpos($file[0], $webroot) === 0) {
                $file = null;
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
        Yii::$app->cache->set($key, $paths, 3*24*60*60); // 3 days
        return $paths;
    }
}
