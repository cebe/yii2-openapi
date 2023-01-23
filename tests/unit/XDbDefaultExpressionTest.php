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
        $testFile = Yii::getAlias("@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/mysql/simple/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);

        // see https://mariadb.com/kb/en/timestamp/#examples
        // Only the first timestamp is automatically inserted and updated, other will have value default '0000-00-00 00:00:00'
        $this->changeDbToMariadb();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/maria/simple/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 1);

        $this->changeDbToPgsql();
        $this->deleteTables();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/pgsql/x_db_default_expression_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/pgsql/simple/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 1);
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    public function testEdit()
    {
        // default DB is Mysql ------------------------------------------------
        $this->deleteTables();
        $this->createTablesForEdit();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/mysql/edit/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);

        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTablesForEdit();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/maria/edit/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 1);

        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTablesForEdit();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/pgsql/x_db_default_expression_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/pgsql/edit/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 1);
    }

    private function createTablesForEdit()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'ts' => 'datetime',
            'ts2' => 'datetime',
            'ts3' => 'datetime',
            'ts4' => 'timestamp',
            'ts5' => 'timestamp',
            'ts6' => 'timestamp',
            'd' => 'date',
            'd2' => 'text',
            'd3' => 'text',
            'ts7' => 'date',
        ])->execute();
    }

    public function testEditExpression()
    {
        // default DB is Mysql ------------------------------------------------
        $this->deleteTables();
        $this->createTablesForEditExpression();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/mysql/edit_expression/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 1);

        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTablesForEditExpression();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/maria/edit_expression/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('maria', 1);

        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTablesForEditExpression();
        $testFile = Yii::getAlias("@specs/x_db_default_expression/pgsql/x_db_default_expression_pgsql.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/x_db_default_expression/pgsql/edit_expression/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('pgsql', 1);
    }

    private function createTablesForEditExpression()
    {
        $mysqlColumns = [
            'ts' => 'datetime DEFAULT \'2011-11-11 00:00:00\'',
            'ts2' => 'datetime DEFAULT CURRENT_TIMESTAMP',
            'ts3' => 'datetime DEFAULT CURRENT_TIMESTAMP',
            'ts4' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'ts5' => 'timestamp DEFAULT \'2011-11-11 00:00:00\'',
            'ts6' => 'timestamp DEFAULT CURRENT_TIMESTAMP',
            'd' => 'date DEFAULT \'2011-11-11\'',
            'd2' => 'text', // DEFAULT "2011-11-11"
            'd3' => 'text', // DEFAULT CURRENT_DATE + INTERVAL 1 YEAR
            'ts7' => 'date DEFAULT (CURRENT_DATE + INTERVAL 2 YEAR)',
        ];
        if (ApiGenerator::isPostgres()) {
            $pgsqlColumns = $mysqlColumns;
            $pgsqlColumns['ts7'] = 'date DEFAULT (CURRENT_DATE + INTERVAL \'2 YEAR\')';
            Yii::$app->db->createCommand()->createTable('{{%fruits}}', $pgsqlColumns)->execute();
            return;
        }

        Yii::$app->db->createCommand()->createTable('{{%fruits}}', $mysqlColumns)->execute();
    }
}
