<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\DbTestCase;
use Yii;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;


class NewColumnPositionTest extends DbTestCase
{
    public function testAddOneNewColumnAtFirstPosition()
    {
        // default DB is Mysql ------------------------------------------------
        $this->deleteTables();
        $this->createTables();
        $testFile = Yii::getAlias("@specs/new_column_position/new_column_position.php");
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

        // $this->changeDbToMariadb();
        // $this->deleteTables();
        // $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        // $this->runGenerator($testFile, 'maria');
        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        //     'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/maria/app"), [
        //     'recursive' => true,
        // ]);
        // $this->checkFiles($actualFiles, $expectedFiles);
        // $this->runActualMigrations('maria', 3);
    }

    private function createTables()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'email' => 'text'
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%twocols}}', [
            'name' => 'text',
            'address' => 'text',
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%dropfirstcols}}', [
            'name' => 'text',
            'address' => 'text',
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%dropfirsttwocols}}', [
            'name' => 'text',
            'address' => 'text',
            'last_name' => 'text',
            'email' => 'text',
        ])->execute();
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%twocols}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%dropfirstcols}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%dropfirsttwocols}}')->execute();
    }
}
