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

class XDbDefaultExpressionTest extends DbTestCase
{
    public function testSimple()
    {
        // default DB is Mysql ------------------------------------------------
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/x_db_default_expression.php");
        $this->runGenerator($testFile, 'mysql');
        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        //     'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/mysql/app"), [
        //     'recursive' => true,
        // ]);
        // $this->checkFiles($actualFiles, $expectedFiles);
        // $this->runActualMigrations('mysql', 3);

        $this->changeDbToMariadb();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/x_db_default_expression.php");
        $this->runGenerator($testFile, 'maria');
        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        //     'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/maria/app"), [
        //     'recursive' => true,
        // ]);
        // $this->checkFiles($actualFiles, $expectedFiles);
        // $this->runActualMigrations('maria', 3);

        // $this->changeDbToPgsql();
        // $this->deleteTables();
        // $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        // $this->runGenerator($testFile, 'pgsql');
        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        //     'except' => ['migrations_mysql_db', 'migrations_maria_db']
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/pgsql/app"), [
        //     'recursive' => true,
        // ]);
        // $this->checkFiles($actualFiles, $expectedFiles);
        // $this->runActualMigrations('pgsql', 3);
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function createTables()
    {
        Yii::$app->db->createCommand()->createTable('{{%editcolumns}}', [
            'id' => 'pk',
            'connection' => 'string',
            'camelCaseCol' => 'string',
        ])->execute();
    }
}
