<?php

namespace tests\unit;

use Yii;
use tests\DbTestCase;
use yii\helpers\FileHelper;

class ChangeColumnNameTest extends DbTestCase
{
    public function testChangeInColumnName()
    {
        // default is MySQL DB
        $this->deleteTables();
        $this->createTableForTest();
        $testFile = Yii::getAlias("@specs/change_column_name/mysql/change_column_name.php");
        $this->runGenerator($testFile, 'mysql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_maria_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/change_column_name/mysql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);

        // Maria DB
        $this->changeDbToMariadb();
        $this->deleteTables();
        $this->createTableForTest();
        $testFile = Yii::getAlias("@specs/change_column_name/mysql/change_column_name.php");
        $this->runGenerator($testFile, 'maria');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_pgsql_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/change_column_name/maria/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);

        // Pgsql
        $this->changeDbToPgsql();
        $this->deleteTables();
        $this->createTableForTest();
        $testFile = Yii::getAlias("@specs/change_column_name/pgsql/change_column_name.php");
        $this->runGenerator($testFile, 'pgsql');
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
            'except' => ['migrations_mysql_db', 'migrations_maria_db']
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/change_column_name/pgsql/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
        $this->deleteTables();
    }

    private function createTableForTest()
    {
        Yii::$app->db->createCommand()->createTable('{{%column_name_changes}}', [
            'id' => 'pk',
            'name' => 'varchar(255) NOT NULL',
            'updated_at' => 'datetime NOT NULL',
        ])->execute();
    }

    private function deleteTables()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%column_name_changes}}')->execute();
    }
}
