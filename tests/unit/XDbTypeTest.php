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
        $this->compareFiles($actualFiles, $expectedFiles);

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
        $this->compareFiles($actualFiles, $expectedFiles);

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
        $this->compareFiles($actualFiles, $expectedFiles);
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
        $this->compareFiles($actualFiles, $expectedFiles);

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
        $this->compareFiles($actualFiles, $expectedFiles);

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
        $this->compareFiles($actualFiles, $expectedFiles);
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
        $this->compareFiles($actualFiles, $expectedFiles);

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
        $this->compareFiles($actualFiles, $expectedFiles);

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
        $this->compareFiles($actualFiles, $expectedFiles);
    }

    protected function compareFiles(array $actual, array $expected)
    {
        self::assertEquals(
            count($actual),
            count($expected)
        );
        foreach ($actual as $index => $file) {
            $expectedFilePath = $expected[$index];
            self::assertFileExists($file);
            self::assertFileExists($expectedFilePath);

            $this->assertFileEquals($expectedFilePath, $file, "Failed asserting that file contents of\n$file\nare equal to file contents of\n$expectedFilePath");
        }
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
            'json_col' => 'json',
            'json_col_2' => 'json',
            'numeric_col' => 'integer',
        ])->execute();
    }
}
