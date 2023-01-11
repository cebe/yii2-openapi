<?php

namespace tests\unit;

use cebe\yii2openapi\lib\ColumnToCode;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\Attribute;
use cebe\yii2openapi\generator\ApiGenerator;
use cebe\yii2openapi\lib\migrations\MysqlMigrationBuilder;
use tests\DbTestCase;
use Yii;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\ColumnSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use function array_filter;
use function getenv;
use function strpos;

class MultiDbFreshMigrationTest extends DbTestCase
{
    public function testMaria()
    {
        $dbName = 'maria';
        Yii::$app->set('db', Yii::$app->maria);
        $this->assertInstanceOf(MySqlSchema::class, Yii::$app->db->schema);
        $testFile = Yii::getAlias('@specs/blog.php');
        $this->runGenerator($testFile, $dbName);
        $expectedFiles = $this->findExpectedFiles($testFile, $dbName);
        $actualFiles = $this->findActualFiles();
        $this->assertEquals($expectedFiles, $actualFiles);
        $this->compareFiles($expectedFiles, $testFile);
    }

    public function testPostgres()
    {
        $dbName = 'pgsql';
        Yii::$app->set('db', Yii::$app->pgsql);
        $this->assertInstanceOf(PgSqlSchema::class, Yii::$app->db->schema);
        $testFile = Yii::getAlias('@specs/blog.php');
        $this->runGenerator($testFile, $dbName);
        $expectedFiles = $this->findExpectedFiles($testFile, $dbName);
        $actualFiles = $this->findActualFiles();
        $this->assertEquals($expectedFiles, $actualFiles);
        $this->compareFiles($expectedFiles, $testFile);
    }

    public function testMysql()
    {
        $dbName = 'mysql';
        Yii::$app->set('db', Yii::$app->mysql);
        $this->assertInstanceOf(MySqlSchema::class, Yii::$app->db->schema);
        $testFile = Yii::getAlias('@specs/blog.php');
        $this->runGenerator($testFile, $dbName);
        $expectedFiles = $this->findExpectedFiles($testFile, $dbName);
        $actualFiles = $this->findActualFiles();
        $this->assertEquals($expectedFiles, $actualFiles);
        $this->compareFiles($expectedFiles, $testFile);
    }

    protected function setUp()
    {
        if (getenv('IN_DOCKER') !== 'docker') {
            $this->markTestSkipped('For docker env only');
        }
        $this->prepareTempDir();
        $this->mockApplication();
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (getenv('IN_DOCKER') === 'docker') {
            $this->destroyApplication();
        }
    }

    protected function runGenerator($configFile, string $dbName = 'mysql')
    {
        $config = require $configFile;
        $config['migrationPath'] = "@app/migrations_{$dbName}_db/";
        $generator = new ApiGenerator($config);
        self::assertTrue($generator->validate(), print_r($generator->getErrors(), true));

        $codeFiles = $generator->generate();
        foreach ($codeFiles as $file) {
            $file->save();
        }
    }

    protected function compareFiles(array $expected, string $testFile)
    {
        foreach ($expected as $file) {
            $expectedFile = str_replace('@app', substr($testFile, 0, -4), $file);
            $actualFile = str_replace('@app', Yii::getAlias('@app'), $file);
            self::assertFileExists($expectedFile);
            self::assertFileExists($actualFile);
            $this->assertFileEquals($expectedFile, $actualFile, "Failed asserting that file contents of\n$actualFile\nare equal to file contents of\n$expectedFile");
        }
    }

    protected function findActualFiles():array
    {
        $actualFiles =  array_map(function($file) {
            return '@app' . substr($file, strlen(Yii::getAlias('@app')));
        },
            FileHelper::findFiles(Yii::getAlias('@app'), ['recursive' => true]));
        \sort($actualFiles);
        return $actualFiles;
    }

    protected function findExpectedFiles(string $testFile, string $dbName):array
    {
        $expectedFiles = array_map(function($file) use ($testFile) {
            return '@app' . substr($file, strlen($testFile) - 4);
        },
            FileHelper::findFiles(substr($testFile, 0, -4), ['recursive' => true]));

        $expectedFiles = array_filter($expectedFiles,
            function($file) use ($dbName) {
                return strpos($file, 'models') !== false || strpos($file, $dbName) !== false;
            });
        \sort($expectedFiles);
        return $expectedFiles;
    }

    public function testFindPosition()
    {
        $dbName = 'mysql';
        Yii::$app->set('db', Yii::$app->mysql);
        $this->assertInstanceOf(MySqlSchema::class, Yii::$app->db->schema);

        $table = Yii::$app->db->getTableSchema('{{%users_after}}');
        if (!$table) {
            Yii::$app->db->createCommand()->createTable('{{%users_after}}', [
                'id' => 'pk',
                'username' => 'string',
                // 'email' => 'string',
            ])->execute();
        }

        $dbModel = new DbModel([
            'name' => 'User',
            'tableName' => 'users_after',
            'description' => 'The User',
            'attributes' => [
                'email_2' => (new Attribute('email_2', ['phpType' => 'string', 'dbType' => 'string']))
                    ->setSize(200)->setRequired()->setFakerStub('substr($faker->safeEmail, 0, 200)'),
                'id' => (new Attribute('id', ['phpType' => 'int', 'dbType' => 'pk']))
                    ->setReadOnly()->setRequired()->setIsPrimary()->setFakerStub('$uniqueFaker->numberBetween(0, 1000000)'),
                'email' => (new Attribute('email', ['phpType' => 'string', 'dbType' => 'string']))
                    ->setSize(200)->setRequired()->setFakerStub('substr($faker->safeEmail, 0, 200)'),
                'username' => (new Attribute('username', ['phpType' => 'string', 'dbType' => 'string']))
                    ->setSize(200)->setRequired()->setFakerStub('substr($faker->userName, 0, 200)'),
                'email_3' => (new Attribute('email_3', ['phpType' => 'string', 'dbType' => 'string']))
                    ->setSize(200)->setRequired()->setFakerStub('substr($faker->safeEmail, 0, 200)'),
            ],
        ]);

        $builder = new MysqlMigrationBuilder(Yii::$app->db, $dbModel);
        $builder->build();
        $name = $builder->findPosition(new ColumnSchema(['name' => 'email']));
        $this->assertSame($name, 'AFTER id');
        $name_2 = $builder->findPosition(new ColumnSchema(['name' => 'email_2']));
        $this->assertSame($name_2, 'FIRST');
        $name_3 = $builder->findPosition(new ColumnSchema(['name' => 'email_3']));
        $this->assertNull($name_3);
    }

    public function testAfterKeyword()
    {
        $dbName = 'mysql';
        Yii::$app->set('db', Yii::$app->mysql);
        $this->assertInstanceOf(MySqlSchema::class, Yii::$app->db->schema);

        $dbSchema = Yii::$app->db->schema;
        $columnSchema = new ColumnSchema([
            'type' => 'integer',
            'dbType' => \version_compare($version, '8.0.17', '>') ? 'int unsigned' : 'int(11) unsigned',
            'phpType' => 'integer',
            'allowNull' => true,
            'autoIncrement' => false,
            'enumValues' => null,
            'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
            'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
            'scale' => null,
            'defaultValue' => 1,
        ]);

        $column = new ColumnToCode(
            $dbSchema, 'tableName', $columnSchema, false, false, false, false, 'AFTER username'
        );
        $columnWithoutPreviousCol = new ColumnToCode(
            $dbSchema, 'tableName', $columnSchema, false, false
        );

        $this->assertContains('AFTER username', $column->getCode());
        $this->assertNotContains('AFTER username', $columnWithoutPreviousCol->getCode());

        // test `after` for fluent part in function call `after()`
        unset($column, $columnWithoutPreviousCol);

        $column = new ColumnToCode(
            $dbSchema, 'tableName', $columnSchema, true, false, false, false, 'AFTER username'
        );
        $columnWithoutPreviousCol = new ColumnToCode(
            $dbSchema, 'tableName', $columnSchema, true, false
        );

        $this->assertContains("->after('username')", $column->getCode());
        $this->assertNotContains("->after('username')", $columnWithoutPreviousCol->getCode());
    }
}
