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
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%pristines}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%newcolumn}}')->execute();
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%editcolumn}}')->execute();

        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%alldbdatatypes}}')->execute();

        $testFile = Yii::getAlias("@specs/x_db_type/x_db_type_mysql.php");
        $this->runGenerator($testFile, 'mysql');

        // $this->changeDbToMariadb();
        // $testFile = Yii::getAlias("@specs/x_db_type/maria/petstore_x_db_type.php");
        // $this->runGenerator($testFile, 'maria');

        // $this->changeDbToPgsql();
        // $testFile = Yii::getAlias("@specs/x_db_type/pgsql/petstore_x_db_type.php");
        // $this->runGenerator($testFile, 'pgsql');
    }

    public function testXDbTypeSecondaryWithNewColumn() // v2
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%animals}}')->execute();
        Yii::$app->db->createCommand()->createTable('{{%animals}}', [
            'id' => 'pk',
            'name' => 'text not null',
            'tag' => 'text null',
        ])->execute();

        $testFile = Yii::getAlias("@specs/x_db_type/petstore_x_db_type_v2.php");
        $this->runGenerator($testFile, 'mysql');
    }

    public function testXDbTypeSecondaryWithEditColumn() // v3
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%animals}}')->execute();
        Yii::$app->db->createCommand()->createTable('{{%animals}}', [
            'id' => 'pk',
            'name' => 'varchar(255) not null default "Horse"',
            'tag' => 'text null',
        ])->execute();

        $testFile = Yii::getAlias("@specs/x_db_type/petstore_x_db_type_v3.php");
        $this->runGenerator($testFile, 'mysql');
    }

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
