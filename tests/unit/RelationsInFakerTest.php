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
        $inbSortedFakers = [];
        foreach($fakers as $fakerFile) {
            $className = 'app\\models\\' . StringHelper::basename($fakerFile, '.php');
            $faker = new $className;

            $modelClassName = str_replace(
                'Faker',
                '',
                StringHelper::basename($fakerFile, '.php')
            );

            if (!method_exists($className, 'dependentOn')) {
                // array_unshift($inbSortedFakers, $fakerFile);
                $inbSortedFakers[$modelClassName] = null;
            } else {
                $inbSortedFakers[$modelClassName] = $className::dependentOn();
                // array_push($inbSortedFakers, $fakerFile);
            }
        }
        VarDumper::dump($inbSortedFakers);
        $newFakers = [];
        foreach ($inbSortedFakers as $modelName => $dependency) {
            if ($dependency === null) {
                // if (!in_array($modelName, $newFakers)) {
                //     array_unshift($newFakers, $modelName);
                // }
                if ($key = array_search($modelName, $newFakers)) {
                    unset($newFakers[$key]);
                }
                array_unshift($newFakers, $modelName);
            } else {
                // resolve dependencies first
                static::f123($dependency, $inbSortedFakers, $newFakers);
                // if (!in_array($modelName, $newFakers)) {
                //     array_push($newFakers, $modelName);
                // }
                if ($key = array_search($modelName, $newFakers)) {
                    unset($newFakers[$key]);
                }
                array_push($newFakers, $modelName);
            }
        }

        $this->assertNull($newFakers);
    }

    public static function f123($dependency, $inbSortedFakers, &$newFakers)
    {
        foreach ($dependency as $aDependency) {
            if ($inbSortedFakers[$aDependency] === null) {
                // if (!in_array($aDependency, $newFakers)) {
                //     array_unshift($newFakers, $aDependency);
                // }
                if ($key = array_search($aDependency, $newFakers)) {
                    unset($newFakers[$key]);
                }
                array_unshift($newFakers, $aDependency);
            } else {
                static::f123($inbSortedFakers[$aDependency], $inbSortedFakers, $newFakers);
            }
        }
    }
}
