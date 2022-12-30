<?php

namespace tests\unit;

use Yii;
use tests\DbTestCase;
use yii\helpers\FileHelper;

// This class contains tests for various issues present at GitHub
class IssueFixTest extends DbTestCase
{
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
}
