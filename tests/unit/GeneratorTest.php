<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\TestCase;
use tests\DbTestCase;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use function strpos;

// class GeneratorTest extends TestCase
class GeneratorTest extends DbTestCase
{
    public function provideTestcases()
    {
        $tests = FileHelper::findFiles(Yii::getAlias('@specs'), ['recursive' => false, 'only' => ['*.php']]);
        $ret = [];
        foreach ($tests as $testFile) {
//            if(!StringHelper::endsWith($testFile, 'ref_noobject.php')){
//                continue;
//            }
            $ret[] = [substr($testFile, strlen(Yii::getAlias('@specs')) + 1)];
        }
        return $ret;
    }

    /**
     * @dataProvider provideTestcases
     */
    public function testGenerate($testFile)
    {
        $testFile = Yii::getAlias("@specs/$testFile");

        $this->prepareTempDir();

        $this->mockApplication();
        // $this->mockApplication($this->mockDbSchemaAsEmpty());

        // if ($testFile !== '/app/tests/specs/petstore.php') {
        //     return;
        // }

        if ($testFile === '/app/tests/specs/postgres_custom.php' ||
            $testFile === '/app/tests/specs/menu.php'
        ) { // TODO docs + add separate tests for this + refactor tests
            $dbo = Yii::$app->db;
            Yii::$app->set('db', Yii::$app->pgsql);
        }

        $generator = $this->createGenerator($testFile);
        $this->assertTrue($generator->validate(), print_r($generator->getErrors(), true));

        $codeFiles = $generator->generate();
        foreach ($codeFiles as $file) {
            $file->save();
        }

        // TODO docs + add separate tests for this + refactor tests
        if ($testFile === '/app/tests/specs/blog_v2.php') {
            FileHelper::removeDirectory('/app/tests/tmp/docker_app/migrations_mysql_db');
            FileHelper::removeDirectory('/app/tests/tmp/docker_app/migrations');
            FileHelper::createDirectory('/app/tests/tmp/docker_app/migrations');
            FileHelper::copyDirectory('/app/tests/specs/blog_v2/migrations', '/app/tests/tmp/docker_app/migrations');
        }
        if ($testFile === '/app/tests/specs/postgres_custom.php') {
            FileHelper::removeDirectory('/app/tests/tmp/docker_app/migrations_pgsql_db');
            FileHelper::removeDirectory('/app/tests/tmp/docker_app/migrations');
            FileHelper::createDirectory('/app/tests/tmp/docker_app/migrations');
            FileHelper::copyDirectory('/app/tests/specs/postgres_custom/migrations', '/app/tests/tmp/docker_app/migrations');
        }

        $expectedFiles = array_map(function($file) use ($testFile) {
            return '@app' . substr($file, strlen($testFile) - 4);
        },
            FileHelper::findFiles(substr($testFile, 0, -4), ['recursive' => true]));
        $actualFiles = array_map(function($file) {
            return '@app' . substr($file, strlen(Yii::getAlias('@app')));
        },
            FileHelper::findFiles(Yii::getAlias('@app'), ['recursive' => true]));

        // Skip database-specific migrations and json-api controllers
        $expectedFiles = array_filter($expectedFiles,
            function($file) {
                return strpos($file, 'migrations_') === false && strpos($file, 'jsonapi') === false;
            });
        $actualFiles = array_filter($actualFiles,
            function($file) {
                return strpos($file, 'migrations_') === false && strpos($file, 'jsonapi') === false;
            });

        sort($expectedFiles);
        sort($actualFiles);
        $this->assertEquals($expectedFiles, $actualFiles);

        foreach ($expectedFiles as $file) {
            $expectedFile = str_replace('@app', substr($testFile, 0, -4), $file);
            $actualFile = str_replace('@app', Yii::getAlias('@app'), $file);
            $this->assertFileExists($expectedFile);
            $this->assertFileExists($actualFile);
            // exec('cp '.$actualFile.' '.$expectedFile);
            $this->assertFileEquals($expectedFile, $actualFile, "Failed asserting that file contents of\n$actualFile\nare equal to file contents of\n$expectedFile");
        }

        if ($testFile === '/app/tests/specs/postgres_custom.php' ||
            $testFile === '/app/tests/specs/menu.php'
        ) {
            Yii::$app->set('db', $dbo); // Mysql is default so set it back
        }
    }

    protected function createGenerator($configFile)
    {
        $config = require $configFile;
        return new ApiGenerator($config);
    }
}
