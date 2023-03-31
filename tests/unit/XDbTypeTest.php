<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\DbTestCase;
use Yii;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\validators\DateValidator;
use function array_filter;
use function getenv;
use function strpos;

class XDbTypeTest extends DbTestCase
{
    public function testXDbTypeFresh()
    {
        // default DB is Mysql ------------------------------------------------
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/mysql/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/fresh/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 4);
        $this->deleteTables();

        // same yaml file is used for MySQL and MariaDB ----------------------
        $this->changeDbToMariadb();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/mysql/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/fresh/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 4);
        $this->deleteTables();

        // PgSQL ------------------------------------------------
        $this->changeDbToPgsql();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/pgsql/x_db_type_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/fresh/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 4);
        $this->deleteTables();
    }

    public function testXDbTypeSecondaryWithNewColumn() // v2
    {
        $this->deleteTables();
        $this->createTableForNewColumns();
        // same yaml file is used as of 'fresh'. Instead of changing the yaml we create new table in db with certain specific columns and then run API generator
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/mysql/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/new_column/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 4);
        $this->deleteTables();

        // same yaml file is used for MySQL and MariaDB ----------------------
        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTableForNewColumns();
        // same yaml file is used as of 'fresh'. Instead of changing the yaml we create new table in db with certain specific columns and then run API generator
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/mysql/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/new_column/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 4);
        $this->deleteTables();

        // PgSQL ------------------------------------------------
        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTableForNewColumns();
        // same yaml file is used as of 'fresh'. Instead of changing the yaml we create new table in db with certain specific columns and then run API generator
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/pgsql/x_db_type_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/new_column/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 4);
        $this->deleteTables();
    }

    public function testXDbTypeSecondaryWithEditColumn() // v3
    {
        $this->deleteTables();
        $this->createTableForEditColumns();
        // same yaml file is used as of 'fresh'. Instead of changing the yaml we create new table in db with certain specific columns and then run API generator
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/mysql/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/edit_column/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 4);
        $this->deleteTables();

        // same yaml file is used for MySQL and MariaDB ----------------------
        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTableForEditColumns();
        // same yaml file is used as of 'fresh'. Instead of changing the yaml we create new table in db with certain specific columns and then run API generator
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/mysql/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/edit_column/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 4);
        $this->deleteTables();

        // PgSQL ------------------------------------------------
        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTableForEditColumns();
        // same yaml file is used as of 'fresh'. Instead of changing the yaml we create new table in db with certain specific columns and then run API generator
        $testFile = Yii::getAlias("@specs/x_db_type/fresh/pgsql/x_db_type_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/edit_column/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 4);
        $this->deleteTables();
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%newcolumns}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%editcolumns}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%alldbdatatypes}}')->execute();
    }

    private function createTableForNewColumns()
    {
        Yii::$app->db->createCommand()->createTable('{{%newcolumns}}', [
            'id' => 'pk',
            'name' => 'string not null',
        ])->execute();
    }

    private function createTableForEditColumns()
    {
        Yii::$app->db->createCommand()->createTable('{{%editcolumns}}', [
            'id' => 'pk',
            'name' => 'varchar(255) not null default \'Horse\'',
            'tag' => 'text null',
            'string_col' => 'string not null',
            'dec_col' => 'decimal(12, 4)',
            'str_col_def' => 'string default \'hi there\'',
            'json_col' => 'json', // json is jsonb in Pgsql via Yii Pgsql Schema
            'json_col_2' => 'json',
            'numeric_col' => 'integer',
        ])->execute();
    }

    public function testValidationRules()
    {
        $this->deleteTables();

        // remove
        // $this->removeStaleMigrationsRecords();

        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_type/rules_and_more/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/rules_and_more/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runUpMigrations('mysql', 4);
        Yii::$app->db->schema->refresh();
        $this->runFaker();
        $this->runDownMigrations('mysql', 4);
        FileHelper::removeDirectory(Yii::getAlias('@app').'/models');
        $this->deleteTables();

        // MariaDB
        $this->changeDbToMariadb();
        // $this->removeStaleMigrationsRecords();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_type/rules_and_more/x_db_type_maria.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/rules_and_more/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runUpMigrations('maria', 4);
        Yii::$app->db->schema->refresh();
        $this->runFaker();
        $this->runDownMigrations('maria', 4);
        FileHelper::removeDirectory(Yii::getAlias('@app').'/models');
        $this->deleteTables();

        // for PgSQL
        $this->changeDbToPgsql();
        // $this->removeStaleMigrationsRecords();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_type/rules_and_more/x_db_type_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_type/rules_and_more/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runUpMigrations('pgsql', 4);
        Yii::$app->db->schema->refresh();
        $this->runFaker();
        $this->runDownMigrations('pgsql', 4);
        FileHelper::removeDirectory(Yii::getAlias('@app').'/models');
        $this->deleteTables();
    }

    // private function removeStaleMigrationsRecords()
    // {
    //     Yii::$app->db->createCommand()->delete('{{%migration}}', 'apply_time >   1675687069')->execute();
    // }
}
