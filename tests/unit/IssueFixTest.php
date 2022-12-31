<?php

namespace tests\unit;

use Yii;
use tests\DbTestCase;
use yii\helpers\FileHelper;

// This class contains tests for various issues present at GitHub
class IssueFixTest extends DbTestCase
{
    // TODO WIP resume from here
    // fix https://github.com/cebe/yii2-openapi/issues/107
    // 107_no_syntax_error
    public function testMigrationsAreNotGeneratedWithSyntaxError()
    {
        // $testFile = Yii::getAlias("@specs/issue_fix/no_syntax_error_107/mysql/no_syntax_error_107.php");
        // $this->runGenerator($testFile, 'mysql');
        // $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
        //     'recursive' => true,
        // ]);
        // $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/id_not_in_rules/app"), [
        //     'recursive' => true,
        // ]);
        // $this->checkFiles($actualFiles, $expectedFiles);

        // $this->changeDbToMariadb();
        // $this->deleteTablesForNoSyntaxError107();
        // $this->createTableForNoSyntaxError107();
        // $testFile = Yii::getAlias("@specs/issue_fix/no_syntax_error_107/mysql/no_syntax_error_107.php");
        // $this->runGenerator($testFile, 'maria');

        $this->changeDbToPgsql();
        $this->deleteTablesForNoSyntaxError107();
        $this->createTableForNoSyntaxError107();
        $testFile = Yii::getAlias("@specs/issue_fix/no_syntax_error_107/mysql/no_syntax_error_107.php");
        $this->runGenerator($testFile, 'pgsql');
    }

    private function deleteTablesForNoSyntaxError107()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function createTableForNoSyntaxError107()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'name' => 'varchar(255)',
        ])->execute();
    }

    public function testFloatIssue()
    {
        // test no migrations are generaeted
        $this->changeDbToPgsql();
        $this->deleteTablesForFloatIssue();
        $this->createTableForFloatIssue();
        $testFile = Yii::getAlias("@specs/issue_fix/float_issue/float_issue.php");
        $this->runGenerator($testFile, 'pgsql');
        $this->expectException(\yii\base\InvalidArgumentException::class);
        FileHelper::findDirectories(Yii::getAlias('@app').'/migration');
        FileHelper::findDirectories(Yii::getAlias('@app').'/migrations');
        FileHelper::findDirectories(Yii::getAlias('@app').'/migrations_mysql_db');
        FileHelper::findDirectories(Yii::getAlias('@app').'/migrations_maria_db');
        FileHelper::findDirectories(Yii::getAlias('@app').'/migrations_pgsql_db');
    }

    private function deleteTablesForFloatIssue()
    {
        Yii::$app->db->createCommand('DROP TABLE IF EXISTS {{%fruits}}')->execute();
    }

    private function createTableForFloatIssue()
    {
        Yii::$app->db->createCommand()->createTable('{{%fruits}}', [
            'id' => 'pk',
            'vat_percent' => 'float default 0',
        ])->execute();
    }
}
