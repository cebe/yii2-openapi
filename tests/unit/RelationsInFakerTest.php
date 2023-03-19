<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\DbTestCase;
use Yii;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\helpers\StringHelper;
use yii\validators\DateValidator;
use function array_filter;
use function getenv;
use function strpos;

class RelationsInFakerTest extends DbTestCase
{
    public function testIndex()
    {
        $testFile = Yii::getAlias("@specs/relations_in_faker/relations_in_faker.php");
        $this->runGenerator($testFile, 'mysql');

        $fakers = FileHelper::findFiles(\Yii::getAlias('@app/models'), [
            'only' => ['*Faker.php'],
            'except' => ['BaseModelFaker.php'],
        ]);
        $modelsDepends = $newFakers = [];
        foreach($fakers as $fakerFile) {
            $className = 'app\\models\\' . StringHelper::basename($fakerFile, '.php');
            $faker = new $className;

            $modelClassName = str_replace(
                'Faker',
                '',
                StringHelper::basename($fakerFile, '.php')
            );

            if (!method_exists($className, 'dependentOn')) {
                $modelsDepends[$modelClassName] = null;
            } else {
                $modelsDepends[$modelClassName] = $className::dependentOn();
            }
        }

        $noDependent = array_filter($modelsDepends, function ($elm) {
            return $elm === null;
        });

        $dependent = array_filter($modelsDepends, function ($elm) {
            return $elm !== null;
        });

        $justDepenentModels = array_keys($dependent);
        $justDepenentModelsClone = $justDepenentModels;


        foreach ($justDepenentModels as $model) {
            if ($modelsDepends[$model] !== null) {
                foreach ($modelsDepends[$model] as $dependentOn) {
                    if ($modelsDepends[$dependentOn] !== null) {
                        // move $dependentOn before $model in clone

                        // d123
                        // in that function if it is already before (sorted) then avoid it
                        static::d123($justDepenentModelsClone, $dependentOn, $model);
                    }
                }
            }
        }

        $this->assertNull($justDepenentModelsClone);
    }

    public static function d123(&$justDepenentModelsClone, $dependentOn, $model)
    {
        $modelKey = array_search($model, $justDepenentModelsClone);
        $depKey = array_search($dependentOn, $justDepenentModelsClone);
        if ($depKey < $modelKey) {
            return;
        }

        unset($justDepenentModelsClone[$depKey]);

        $restRight = array_slice($justDepenentModelsClone, $modelKey);
        $theKey = (($modelKey) < 0) ? 0 : ($modelKey);
        $restLeft = array_slice($justDepenentModelsClone, 0, $theKey);

        $justDepenentModelsClone = array_merge($restLeft, [$dependentOn], $restRight);
    }
}
