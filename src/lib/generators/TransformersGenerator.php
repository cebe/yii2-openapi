<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\yii2openapi\lib\CodeFiles;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\Transformer;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Yii;
use yii\gii\CodeFile;

class TransformersGenerator
{
    /**
     * @var \cebe\yii2openapi\lib\Config
     */
    protected $config;

    /**
     * @var array|\cebe\yii2openapi\lib\items\DbModel[]
     */
    protected $models;

    /**
     * @var CodeFiles $files
    **/
    protected $files;

    public function __construct(Config $config, array $models)
    {
        $this->config = $config;
        $this->models = array_filter($models, function ($model) {
            return $model instanceof DbModel;
        });
        $this->files = new CodeFiles([]);
    }
    public function generate():CodeFiles
    {
        if (!$this->config->generateControllers || !$this->config->useJsonApi) {
            return $this->files;
        }
        $transformerPath = $this->config->getPathFromNamespace($this->config->transformerNamespace);
        foreach ($this->models as $model) {
            $transformer = Yii::createObject(Transformer::class, [
               $model,
               $this->config->transformerNamespace,
               $this->config->modelNamespace,
               $this->config->singularResourceKeys
           ]);
            $dirPath = $transformerPath . ($this->config->extendableTransformers ? '/base' : '');
            $ns = $this->config->transformerNamespace . ($this->config->extendableTransformers ? '\\base' : '');
            $this->files->add(new CodeFile(
                Yii::getAlias("{$dirPath}/{$transformer->name}.php"),
                $this->config->render('transformer.php', [
                   'namespace' => $ns,
                   'mainNamespace' => $this->config->transformerNamespace,
                   'extendable' => $this->config->extendableTransformers,
                   'transformer' => $transformer,
               ])
            ));
            if (!$this->config->extendableTransformers) {
                continue;
            }
            if (file_exists(Yii::getAlias("$transformerPath/{$transformer->name}.php"))) {
                // only generate custom classes if they do not exist, do not override
                continue;
            }

            $classFileGenerator = new FileGenerator();
            $reflection = new ClassGenerator(
                $transformer->name,
                $this->config->transformerNamespace,
                null,
                $this->config->transformerNamespace . '\\base\\' . $transformer->name
            );
            $classFileGenerator->setClasses([$reflection]);
            $this->files->add(new CodeFile(
                Yii::getAlias("$transformerPath/{$transformer->name}.php"),
                $classFileGenerator->generate()
            ));
        }
        return $this->files;
    }
}
