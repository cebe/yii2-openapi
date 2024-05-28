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

class ForeignKeyColumnNameTest extends DbTestCase
{
    public function testIndex()
    {
        // default DB is Mysql ----------------------------------

        $testFile = Yii::getAlias("@specs/fk_col_name/fk_col_name.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/fk_col_name/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 3);
    }

    public function testIndexForColumnWithCustomName()
    {
        // default DB is Mysql ----------------------------------

        $testFile = Yii::getAlias("@specs/fk_col_name_index/fk_col_name_index.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/fk_col_name_index/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->runActualMigrations('mysql', 3);
    }
}
