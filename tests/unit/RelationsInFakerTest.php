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
        $this->changeDbToPgsql();
        $testFile = Yii::getAlias("@specs/relations_in_faker/relations_in_faker.php");
        $testFileConfig = require $testFile;

        $this->runGenerator($testFile, 'pgsql');

        $fakers = FileHelper::findFiles(\Yii::getAlias('@app/models/fakers'), [
            'only' => ['*Faker.php'],
            'except' => ['BaseModelFaker.php'],
        ]);

        $finalSortedModels = static::sortModels($fakers);

        $this->assertSame($finalSortedModels, [
            'Account',
            'C123',
            'D123',
            'B123',
            'A123',
            'Domain',
            'E123',
            'Routing',
        ]);

        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/relations_in_faker/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 8);
    }

    public static function sortModels(array $fakers, string $fakerNamespace = 'app\\models\\fakers\\')
    {
        $modelsDependencies = [];
        foreach($fakers as $fakerFile) {
            $className = $fakerNamespace . StringHelper::basename($fakerFile, '.php');
            $faker = new $className;

            $modelClassName = str_replace(
                'Faker',
                '',
                StringHelper::basename($fakerFile, '.php')
            );

            if (!method_exists($className, 'dependentOn')) {
                $modelsDependencies[$modelClassName] = null;
            } else {
                $modelsDependencies[$modelClassName] = $className::dependentOn();
            }
        }

        // these models are not dependent on any models
        $standalone = array_filter($modelsDependencies, function ($elm) {
            return $elm === null;
        });

        $dependent = array_filter($modelsDependencies, function ($elm) {
            return $elm !== null;
        });

        $justDepenentModels = array_keys($dependent);
        $sortedDependentModels = $justDepenentModels;
        sort($sortedDependentModels);

        foreach ($justDepenentModels as $model) {
            if ($modelsDependencies[$model] !== null) {
                foreach ($modelsDependencies[$model] as $dependentOn) {
                    if ($modelsDependencies[$dependentOn] !== null) {
                        // move $dependentOn before $model

                        // move model to sort/order
                        // in that function if it is already before (sorted) then avoid it
                        static::moveModel($sortedDependentModels, $dependentOn, $model);
                    }
                }
            }
        }
        $standalone = array_keys($standalone);
        sort($standalone);
        $finalSortedModels = array_merge($standalone, $sortedDependentModels);
        return $finalSortedModels;
    }

    public static function moveModel(&$sortedDependentModels, $dependentOn, $model)
    {
        $modelKey = array_search($model, $sortedDependentModels);
        $depKey = array_search($dependentOn, $sortedDependentModels);
        if ($depKey < $modelKey) {
            return;
        }

        unset($sortedDependentModels[$depKey]);

        $restRight = array_slice($sortedDependentModels, $modelKey);
        $theKey = (($modelKey) < 0) ? 0 : ($modelKey);
        $restLeft = array_slice($sortedDependentModels, 0, $theKey);

        $sortedDependentModels = array_merge($restLeft, [$dependentOn], $restRight);
    }
}
