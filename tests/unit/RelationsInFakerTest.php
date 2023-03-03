<?php

namespace tests\unit;

use cebe\yii2openapi\generator\ApiGenerator;
use tests\DbTestCase;
use Yii;
use yii\db\mysql\Schema as MySqlSchema;
use yii\db\pgsql\Schema as PgSqlSchema;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\validators\DateValidator;
use function array_filter;
use function getenv;
use function strpos;

class RelationsInFakerTest extends DbTestCase
{
    public function testIndex()
    {
        // return;
        $testFile = Yii::getAlias("@specs/relations_in_faker/relations_in_faker.php");
        $this->runGenerator($testFile, 'mysql');
    }
}
