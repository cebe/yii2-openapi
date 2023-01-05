<?php

namespace tests;

use cebe\yii2openapi\generator\ApiGenerator;
use Yii;
use yii\di\Container;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use \SamIT\Yii2\MariaDb\Schema as MariaDbSchema;
use yii\helpers\{ArrayHelper, VarDumper};
use yii\helpers\FileHelper;

class DbTestCase extends \PHPUnit\Framework\TestCase
{
    protected function prepareTempDir()
    {
        FileHelper::removeDirectory(__DIR__ . '/tmp/docker_app');
        FileHelper::createDirectory(__DIR__ . '/tmp/docker_app');
        Yii::setAlias('@app', __DIR__ . '/tmp/docker_app');
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        $fileConfig = require __DIR__ . '/config/console.php';
        new $appClass(ArrayHelper::merge($fileConfig, $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
        Yii::$container = new Container();
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

    protected function changeDbToMysql()
    {
        Yii::$app->set('db', Yii::$app->mysql);
        self::assertInstanceOf(MySqlSchema::class, Yii::$app->db->schema);
        self::assertNotInstanceOf(MariaDbSchema::class, Yii::$app->db->schema);
        self::assertNotInstanceOf(PgSqlSchema::class, Yii::$app->db->schema);
        self::assertTrue(strpos(Yii::$app->db->schema->getServerVersion(), 'MariaDB') === false);
    }

    protected function changeDbToMariadb()
    {
        Yii::$app->set('db', Yii::$app->maria);
        self::assertInstanceOf(MariaDbSchema::class, Yii::$app->db->schema);
        self::assertNotInstanceOf(PgSqlSchema::class, Yii::$app->db->schema);
        self::assertTrue(strpos(Yii::$app->db->schema->getServerVersion(), 'MariaDB') !== false);
    }

    protected function changeDbToPgsql()
    {
        Yii::$app->set('db', Yii::$app->pgsql);
        self::assertNotInstanceOf(MariaDbSchema::class, Yii::$app->db->schema);
        self::assertNotInstanceOf(MySqlSchema::class, Yii::$app->db->schema);
        self::assertInstanceOf(PgSqlSchema::class, Yii::$app->db->schema);
    }

    protected function checkFiles(array $actual, array $expected)
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

    protected function runActualMigrations(string $db = 'mysql', int $number = 2): void
    {
        // up
        exec('cd tests; ./yii migrate-'.$db.' --interactive=0', $upOutput, $upExitCode);
        $last = count($upOutput) - 1;
        $lastThird = count($upOutput) - 3;
        $this->assertSame($upExitCode, 0);
        $this->assertSame($upOutput[$last], 'Migrated up successfully.');
        $this->assertSame($upOutput[$lastThird], $number.' '.(($number === 1) ? 'migration was' : 'migrations were').' applied.');
        // 1 migration was applied.
        // 2 migrations were applied.

        // down
        exec('cd tests; ./yii migrate-'.$db.'/down --interactive=0 '.$number, $downOutput, $downExitCode);
        $last = count($downOutput) - 1;
        $lastThird = count($downOutput) - 3;
        $this->assertSame($downExitCode, 0);
        $this->assertSame($downOutput[$last], 'Migrated down successfully.');
        $this->assertSame($downOutput[$lastThird], $number.' '.(($number === 1) ? 'migration was' : 'migrations were').' reverted.');

    }
}
