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
    public function testEnumFresh()
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

    public function testEnumToString()
    {
        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTableForEditEnumToString();
        $testFile = Yii::getAlias("@specs/enum/enum.php");
        $this->runGenerator($testFile, 'pgsql');
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_device CASCADE')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%newcolumns}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%editcolumns}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%alldbdatatypes}}')->execute();
    }

    private function createTableForEditEnumToString()
    {
        Yii::$app->db->createCommand('CREATE TYPE enum_device AS ENUM(\'MOBILE\', \'TV\', \'COMPUTER\')')->execute();
        Yii::$app->db->createCommand()->createTable('{{%editcolumns}}', [
            'id' => 'pk',
            'device' => 'enum_device NOT NULL DEFAULT \'TV\'',
        ])->execute();
    }
}
