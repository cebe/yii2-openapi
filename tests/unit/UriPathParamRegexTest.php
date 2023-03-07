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

class UriPathParamRegexTest extends DbTestCase
{
    public function testIndex()
    {
        $testFile = Yii::getAlias("@specs/uri_path_param_regex/uri_path_param_regex.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/uri_path_param_regex/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
