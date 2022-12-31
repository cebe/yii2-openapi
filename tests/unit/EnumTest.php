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

// For all scenarios we use same yaml file. It has all necessary data for all schenarios. Also see createTable method in this class
class EnumTest extends DbTestCase
{
    public function testFresh()
    {
        // default DB is Mysql ------------------------------------------------
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 3);

        $this->changeDbToMariadb();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 3);

        $this->changeDbToPgsql();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/fresh/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 3);
    }

    public function testAddNewColumn() // and drop enum column
    {
        // MySQL
        $this->deleteTables();
        $this->createTableForNewEnumColumn();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/new_column/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 3);

        // Mariadb
        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTableForNewEnumColumn();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/new_column/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 3);

        // Pgsql
        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTableForNewEnumColumn();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/new_column/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 3);
    }

    public function testChangeToAndFromEnum() // edit enum to string and vice versa
    {
        $this->deleteTables();
        $this->createTableForEditEnumToString();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/change/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 3);

        // Mariadb
        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTableForEditEnumToString();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/change/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 3);


        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTableForEditEnumToString();
        $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/enum/change/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 3);
    }

    // TODO ENH enum change is more work than just changing the eunm values. And for PgSQL it is even more
    // public function testEnumValuesChange()
    // {
    //     $this->deleteTables();
    //     $this->createTableForEnumValueChange();
    //     $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
    //     $this->runGenerator($testFile, 'mysql');


    //     $this->changeDbToMariadb();
    //     $this->deleteTables();
    //     $this->createTableForEnumValueChange();
    //     $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
    //     $this->runGenerator($testFile, 'maria');


    //     $this->changeDbToPgsql();
    //     $this->deleteTables();
    //     $this->createTableForEnumValueChange();
    //     $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
    //     $this->runGenerator($testFile, 'pgsql');
    // }

    // public function testStringToEnum()
    // {
    //     $this->deleteTables();
    //     $this->createTableForEditEnumToString();
    //     $testFile = Yii::getAlias("@specs/enum/fresh/mysql/enum.php");
    //     $this->runGenerator($testFile, 'mysql');
    // }

    // public function testChangeEnumValues()
    // {
    //     // TODO
    //     // add a value to list
    //     // fix a typo in a enum value present in existing list
    //     // remove a value from list
    // }

    private function deleteTables()
    {
        if (ApiGenerator::isPostgres()) {
            Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_device CASCADE')->execute();
            Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_connection CASCADE')->execute();
            Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_new_column CASCADE')->execute();
            Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_delete_col CASCADE')->execute();
            Yii::$app->db->createCommand('DROP TYPE IF EXISTS enum_add_one_mood_at_last CASCADE')->execute();
        }
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%newcolumns}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%editcolumns}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%alldbdatatypes}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%enumvaluechanges}}')->execute();
    }

    private function createTableForEditEnumToString() // and vice versa
    {
        if (ApiGenerator::isPostgres()) {
            Yii::$app->db->createCommand('CREATE TYPE enum_device AS ENUM(\'MOBILE\', \'TV\', \'COMPUTER\')')->execute();
            Yii::$app->db->createCommand()->createTable('{{%editcolumns}}', [
                'id' => 'pk',
                'device' => 'enum_device NOT NULL DEFAULT \'TV\'',
                'connection' => 'string'
            ])->execute();
            return;
        }
        Yii::$app->db->createCommand()->createTable('{{%editcolumns}}', [
            'id' => 'pk',
            'device' => 'enum("MOBILE", "TV", "COMPUTER") NOT NULL DEFAULT \'TV\'',
            'connection' => 'string'
        ])->execute();
    }

    private function createTableForNewEnumColumn()
    {
        if (ApiGenerator::isPostgres()) {
            Yii::$app->db->createCommand('CREATE TYPE enum_delete_col AS ENUM(\'FOUR\', \'FIVE\', \'SIX\')')->execute();
            Yii::$app->db->createCommand()->createTable('{{%newcolumns}}', [
                'id' => 'pk',
                'delete_col' => 'enum_delete_col'
            ])->execute();
            return;
        }

        Yii::$app->db->createCommand()->createTable('{{%newcolumns}}', [
            'id' => 'pk',
            'delete_col' => 'enum("FOUR", "FIVE", "SIX")'
        ])->execute();
    }

    // private function createTableForEnumValueChange()
    // {
    //     // removing a enum value is directly not supported in PgSQL
    //     if (ApiGenerator::isPostgres()) {
    //         Yii::$app->db->createCommand('CREATE TYPE enum_add_one_mood_at_last AS ENUM(\'INTEREST\', \'JOY\', \'NOSTALGIA\')')->execute();
    //         Yii::$app->db->createCommand()->createTable('{{%enumvaluechanges}}', [
    //             'id' => 'pk',
    //             'add_one_mood_at_last' => 'enum_add_one_mood_at_last'
    //         ])->execute();
    //         return;
    //     }

    //     Yii::$app->db->createCommand()->createTable('{{%enumvaluechanges}}', [
    //         'id' => 'pk',
    //         'add_one_mood_at_last' => 'enum("INTEREST", "JOY", "NOSTALGIA")',
    //         'remove_last_mood' => 'enum("INTEREST", "JOY", "NOSTALGIA")',
    //         'add_mood_in_between' => 'enum("INTEREST", "JOY", "NOSTALGIA")',
    //         'rename_last_mood' => 'enum("INTEREST", "JOY", "NOSTALGIA")',
    //     ])->execute();
    // }
}
