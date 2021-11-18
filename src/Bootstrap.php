<?php

namespace cebe\yii2openapi;

use cebe\yii2openapi\interfaces\ControllersGenerator;
use cebe\yii2openapi\interfaces\FakersGenerator;
use cebe\yii2openapi\interfaces\JsonActionGenerator;
use cebe\yii2openapi\interfaces\MigrationsGenerator;
use cebe\yii2openapi\interfaces\ModelsGenerator;
use cebe\yii2openapi\interfaces\RestActionGenerator;
use cebe\yii2openapi\interfaces\TransformersGenerator;
use cebe\yii2openapi\interfaces\UrlRulesGenerator;
use cebe\yii2openapi\lib\generators\DefaultControllersGenerator;
use cebe\yii2openapi\lib\generators\DefaultFakersGenerator;
use cebe\yii2openapi\lib\generators\DefaultJsonActionGenerator;
use cebe\yii2openapi\lib\generators\DefaultMigrationsGenerator;
use cebe\yii2openapi\lib\generators\DefaultModelsGenerator;
use cebe\yii2openapi\lib\generators\DefaultRestActionGenerator;
use cebe\yii2openapi\lib\generators\DefaultTransformersGenerator;
use cebe\yii2openapi\lib\generators\DefaultUrlRulesGenerator;
use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{

    /**
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        if ($app->hasModule('gii')) {
           Yii::$container->setDefinitions([
               ControllersGenerator::class => DefaultControllersGenerator::class,
               FakersGenerator::class => DefaultFakersGenerator::class,
               MigrationsGenerator::class => DefaultMigrationsGenerator::class,
               ModelsGenerator::class => DefaultModelsGenerator::class,
               UrlRulesGenerator::class => DefaultUrlRulesGenerator::class,
               TransformersGenerator::class => DefaultTransformersGenerator::class,
               RestActionGenerator::class => DefaultRestActionGenerator::class,
               JsonActionGenerator::class => DefaultJsonActionGenerator::class
           ]);
        }
    }
}