<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\yii2openapi\lib\CodeFiles;
use cebe\yii2openapi\lib\Config;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Yii;
use yii\gii\CodeFile;

class ModelsGenerator
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
        $this->models = $models;
        $this->files = new CodeFiles([]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function generate():CodeFiles
    {
        if (!$this->config->generateModels) {
            return $this->files;
        }
        $modelPath = $this->config->getPathFromNamespace($this->config->modelNamespace);
        $fakerPath = $this->config->getPathFromNamespace($this->config->fakerNamespace);
        if ($this->config->generateModelFaker) {
            $this->files->add(new CodeFile(
                Yii::getAlias("$fakerPath/BaseModelFaker.php"),
                $this->config->render('basefaker.php', ['namespace' => $this->config->fakerNamespace])
            ));
        }
        foreach ($this->models as $model) {
            $className = $model->getClassName();
            if ($model->isNotDb === false) {
                $this->files->add(new CodeFile(
                    Yii::getAlias("$modelPath/base/$className.php"),
                    $this->config->render(
                        'dbmodel.php',
                        [
                            'model' => $model,
                            'namespace' => $this->config->modelNamespace . '\\base',
                            'relationNamespace' => $this->config->modelNamespace,
                        ]
                    )
                ));
                if ($this->config->generateModelFaker) {
                    $deps = []; # list of all models that this model is dependent on
                    foreach ($model->hasOneRelations as $key => $hasOneRelation) {
                        $deps[] = $model->hasOneRelations[$key]->getClassName();
                    }
                    $deps = array_unique($deps);

                    $this->files->add(new CodeFile(
                        Yii::getAlias("$fakerPath/{$className}Faker.php"),
                        $this->config->render(
                            'faker.php',
                            [
                                'model' => $model,
                                'modelNamespace' => $this->config->modelNamespace,
                                'namespace' => $this->config->fakerNamespace,
                                'deps' => $deps,
                            ]
                        )
                    ));
                }
            } else {
                /** This case not implemented yet, just keep it **/
                $this->files->add(new CodeFile(
                    Yii::getAlias("$modelPath/base/$className.php"),
                    $this->config->render(
                        'model.php',
                        [
                            'model' => $model,
                            'namespace' => $this->config->modelNamespace . '\\base',
                        ]
                    )
                ));
            }

            // only generate custom classes if they do not exist, do not override
            if (!file_exists(Yii::getAlias("$modelPath/$className.php"))) {
                $classFileGenerator = new FileGenerator();
                $reflection = new ClassGenerator(
                    $className,
                    $this->config->modelNamespace,
                    null,
                    $this->config->modelNamespace . '\\base\\' . $className
                );
                $classFileGenerator->setClasses([$reflection]);
                $this->files->add(new CodeFile(
                    Yii::getAlias("$modelPath/$className.php"),
                    $classFileGenerator->generate()
                ));
            }
        }
        return $this->files;
    }
}
