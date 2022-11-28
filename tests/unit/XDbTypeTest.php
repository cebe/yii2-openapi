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
        $testFile = Yii::getAlias("@specs/x_db_type/petstore_x_db_type.php");

        $this->runGenerator($testFile, 'mysql');

        // $expectedFiles = array_map(function($file) use ($testFile) {
        //     return '@app' . substr($file, strlen($testFile) - 4);
        // },
        //     FileHelper::findFiles(substr($testFile, 0, -4), ['recursive' => true]));
        // $actualFiles = array_map(function($file) {
        //     return '@app' . substr($file, strlen(Yii::getAlias('@app')));
        // },
        //     FileHelper::findFiles(Yii::getAlias('@app'), ['recursive' => true]));
        // // pd($actualFiles);

        // // Skip database-specific migrations and json-api controllers
        // $expectedFiles = array_filter($expectedFiles,
        //     function($file) {
        //         return strpos($file, 'migrations_') === false && strpos($file, 'jsonapi') === false;
        //     });
        // $actualFiles = array_filter($actualFiles,
        //     function($file) {
        //         return strpos($file, 'migrations_') === false && strpos($file, 'jsonapi') === false;
        //     });

        // sort($expectedFiles);
        // sort($actualFiles);
        // $this->assertEquals($expectedFiles, $actualFiles);

        // foreach ($expectedFiles as $file) {
        //     $expectedFile = str_replace('@app', substr($testFile, 0, -4), $file);
        //     $actualFile = str_replace('@app', Yii::getAlias('@app'), $file);
        //     $this->assertFileExists($expectedFile);
        //     $this->assertFileExists($actualFile);
        //     $this->assertFileEquals($expectedFile, $actualFile, "Failed asserting that file contents of\n$actualFile\nare equal to file contents of\n$expectedFile");
        // }
    }

    // public function testXDbTypeSecondaryWithNewColumn()
    // {
    //     // TODO load fixture
    //     $testFile = Yii::getAlias("@specs/x_db_type/petstore_x_db_type_v2.php");

    //     $this->generateFiles($testFile);
    // }

    // public function testXDbTypeSecondaryWithEditColumn()
    // {
    //     // TODO load fixture
    //     $testFile = Yii::getAlias("@specs/x_db_type/petstore_x_db_type_v3.php");

    //     $this->generateFiles($testFile);
    // }

    // private function generateFiles(string $testFile): void
    // {
    //     $this->prepareTempDir();
    //     $this->mockApplication();

    //     $generator = $this->createGenerator($testFile);
    //     $this->assertTrue($generator->validate(), print_r($generator->getErrors(), true));

    //     $codeFiles = $generator->generate();
    //     foreach ($codeFiles as $file) {
    //         $file->save();
    //     }
    // }
}
