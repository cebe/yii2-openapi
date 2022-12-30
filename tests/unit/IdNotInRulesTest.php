<?php

namespace tests\unit;

use Yii;
use tests\DbTestCase;
use yii\helpers\FileHelper;

class IdNotInRulesTest extends DbTestCase
{
    public function testIdPkNotInRules()
    {
        $testFile = Yii::getAlias("@specs/id_not_in_rules/id_not_in_rules.php");
        $this->runGenerator($testFile);
        $actualFiles = FileHelper::findFiles(Yii::getAlias('@app'), [
            'recursive' => true,
        ]);
        $expectedFiles = FileHelper::findFiles(Yii::getAlias("@specs/id_not_in_rules/app"), [
            'recursive' => true,
        ]);
        $this->checkFiles($actualFiles, $expectedFiles);
    }
}
