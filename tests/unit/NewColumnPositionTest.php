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
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/new_column_position/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 10);

        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTables();
        $testFile = Yii::getAlias("@specs/new_column_position/new_column_position.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/new_column_position/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 10);
    }

    private function createTables()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'email' => 'text'
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%fruit2s}}', [
            'email' => 'text'
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%twocols}}', [
            'name' => 'text',
            'address' => 'text',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%twocol2s}}', [
            'name' => 'text',
            'address' => 'text',
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%dropfirstcols}}', [
            'name' => 'text',
            'address' => 'text',
        ])->execute();

        // not relavant because data type is fetched from DB and not x-db-type
        // Yii::$app->db->createCommand()->createTable('{{%dropfirstcol2s}}', [
        //     'name POINT',
        //     'address' => 'text',
        // ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%dropfirsttwocols}}', [
            'name' => 'text',
            'address' => 'text',
            'last_name' => 'text',
            'email' => 'text',
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%addtwonewcolinbetweens}}', [
            'name' => 'text',
            'address' => 'text',
            'last_name' => 'text',
            'email' => 'text',
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%addtwonewcolinbetween2s}}', [
            'name' => 'text',
            'address' => 'text',
            'last_name' => 'text',
            'email' => 'text',
        ])->execute();

        Yii::$app->db->createCommand()->createTable('{{%twonewcolatlasts}}', [
            'email' => 'text'
        ])->execute();
        Yii::$app->db->createCommand()->createTable('{{%twonewcolatlast2s}}', [
            'email' => 'text'
        ])->execute();
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruit2s}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%twocols}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%twocol2s}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%dropfirstcols}}')->execute();
        // Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%dropfirstcol2s}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%dropfirsttwocols}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%addtwonewcolinbetweens}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%addtwonewcolinbetween2s}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%twonewcolatlasts}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%twonewcolatlast2s}}')->execute();
    }
}
