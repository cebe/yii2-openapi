<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\DbTestCase;
use Yii;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use function array_filter;
use function getenv;
use function strpos;

class EnumTest extends DbTestCase
{
    public function testXDbTypeFresh()
    {
        // default DB is Mysql ------------------------------------------------
        // $this->deleteTables();
        $testFile = Yii::getAlias("@specs/enum/enum.php");
        $this->runGenerator($testFile, 'mysql');

        $this->changeDbToMariadb();
        $testFile = Yii::getAlias("@specs/enum/enum.php");
        $this->runGenerator($testFile, 'maria');

        $this->changeDbToPgsql();
        $testFile = Yii::getAlias("@specs/enum/enum.php");
        $this->runGenerator($testFile, 'pgsql');


        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        //     'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/fresh/mysql/app"), [
        //     'recursive' => true,
        // ]);
        // $this->compareFiles($actualFiles, $expectedFiles);
    }
}
