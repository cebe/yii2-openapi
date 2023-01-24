<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\DbTestCase;
use Yii;
use cebe\openapi\Reader;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use function array_filter;
use function getenv;
use function strpos;

class XOnXFkConstraintTest extends DbTestCase
{
    public function testSimple()
    {

        // $openapi = Reader::readFromYamlFile(realpath('openapi.yaml'));
        // $openapi = Reader::readFromYamlFile(Yii::getAlias("@specs/x_on_x_fk_constraint/x_on_x_fk_constraint.yaml"));
        // echo PHP_EOL;echo PHP_EOL;
        // foreach ($openapi->components->schemas as $schemaName => $aSchema) {
        //     // VarDumper::dump($schemaName);
        //     if ($schemaName === 'Post') {
        //         VarDumper::dump(
        //             $aSchema->properties['user']->allOf[1]
        //             ->{'x-fk-on-update'}
        //             //->getSerializableData()
        //             //->getExtensions()
        //             //->properties => []
        //         );
        //     }
        // }
        // return;



        // default DB is Mysql ------------------------------------------------
        // $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_on_x_fk_constraint/x_on_x_fk_constraint.php");
        $this->runGenerator($testFile, 'mysql');
        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        //     'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/fresh/mysql/app"), [
        //     'recursive' => true,
        // ]);
        // $this->checkFiles($actualFiles, $expectedFiles);
        // $this->runActualMigrations('mysql', 4);
    }
}
