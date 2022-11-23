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

class MultiDbSecondaryMigrationTest extends DbTestCase
{
    public function testPostgresCustom()
    {
        $dbName = 'pgsql';
        Yii::$app->set('db', Yii::$app->pgsql);
        $this->assertInstanceOf(PgSqlSchema::class, Yii::$app->db->schema);
        $testFile = Yii::getAlias('@specs/postgres_custom.php');
        $this->runGenerator($testFile, $dbName);
        $expectedFiles = $this->findExpectedFiles($testFile, $dbName);
        $actualFiles = $this->findActualFiles();
        $this->assertEquals($expectedFiles, $actualFiles);
        $this->compareFiles($expectedFiles, $testFile);
    }

    public function testMaria()
    {
        $dbName = 'maria';
        Yii::$app->set('db', Yii::$app->maria);
        $this->assertInstanceOf(MySqlSchema::class, Yii::$app->db->schema);
        $testFile = Yii::getAlias('@specs/blog_v2.php');
        $this->runGenerator($testFile, $dbName);
        $expectedFiles = $this->findExpectedFiles($testFile, $dbName);
        $actualFiles = $this->findActualFiles();
        $this->assertEquals($expectedFiles, $actualFiles);
        $this->compareFiles($expectedFiles, $testFile);
    }

    /**
     * @group a123 TODO
     */
    public function testPostgres()
    {
        $dbName = 'pgsql';
        Yii::$app->set('db', Yii::$app->pgsql);
        $this->assertInstanceOf(PgSqlSchema::class, Yii::$app->db->schema);
        $testFile = Yii::getAlias('@specs/blog_v2.php');
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
        $testFile = Yii::getAlias('@specs/blog_v2.php');
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

    protected function runGenerator($configFile, string $dbName)
    {
        $config = require $configFile;
        $config['migrationPath'] = "@app/migrations_{$dbName}_db/";
        $config['generateModels'] = false;
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
        $actualFiles = array_filter($actualFiles, function($file){
            return strpos($file, 'migrations') !== false;
        });
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
                return strpos($file, $dbName) !== false;
            });
        \sort($expectedFiles);
        return $expectedFiles;
    }
}
