<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\TestCase;
use Yii;
use yii\helpers\FileHelper;
use function strpos;

class GeneratorTest extends TestCase
{
    public function provideTestcases()
    {
        $tests = FileHelper::findFiles(Yii::getAlias('@specs'), ['recursive' => false, 'only' => ['*.php']]);
        $ret = [];
        foreach ($tests as $testFile) {
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

        $this->mockApplication($this->mockDbSchemaAsEmpty());

        $generator = $this->createGenerator($testFile);
        $this->assertTrue($generator->validate(), print_r($generator->getErrors(), true));

        $codeFiles = $generator->generate();
        foreach ($codeFiles as $file) {
            $file->save();
        }

        $expectedFiles = array_map(function($file) use ($testFile) {
            return '@app' . substr($file, strlen($testFile) - 4);
        },
            FileHelper::findFiles(substr($testFile, 0, -4), ['recursive' => true]));
        $actualFiles = array_map(function($file) {
            return '@app' . substr($file, strlen(Yii::getAlias('@app')));
        },
            FileHelper::findFiles(Yii::getAlias('@app'), ['recursive' => true]));

        //Skip database-specific migrations and json-api controllers
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
            $this->assertFileEquals($expectedFile, $actualFile, "Failed asserting that file contents of\n$actualFile\nare equal to file contents of\n$expectedFile");
        }
    }

    protected function createGenerator($configFile)
    {
        $config = require $configFile;
        return new ApiGenerator($config);
    }
}
