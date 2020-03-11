<?php

namespace cebe\yii2openapi\generator\helpers;

use yii\base\Component;
use yii\db\Connection;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

class DatabaseDiff extends Component
{
    /**
     * @var string|array|Connection
     */
    public $db = 'db';

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);


    }

    public function diffTable($tableName, $columns)
    {
        $schema = $this->db->getTableSchema($tableName, true);
        if ($schema === null) {
            // create table
            $codeColumns = VarDumper::export(ArrayHelper::map($columns, 'dbName', 'dbType'));
            $upCode = "\$this->createTable('$tableName', $codeColumns);";
            $downCode = "\$this->dropTable('$tableName');";
            return [$upCode, $downCode];
        }

        $upCode = [];
        $downCode = [];

        $wantNames = array_keys($columns);
        $haveNames = $schema->columnNames;
        sort($wantNames);
        sort($haveNames);
        $missingDiff = array_diff($wantNames, $haveNames);
        $unknownDiff = array_diff($haveNames, $wantNames);
        foreach($missingDiff as $missingColumn) {
            $upCode[] = "\$this->addColumn('$tableName', '$missingColumn', '{$columns[$missingColumn]['dbType']}');";
            $downCode[] = "\$this->dropColumn('$tableName', '$missingColumn');";
        }
        foreach($unknownDiff as $unknownColumn) {
            $upCode[] = "\$this->dropColumn('$tableName', '$unknownColumn');";
            $oldDbType = $schema->columns[$unknownColumn]->dbType; // TODO more precise!
            $downCode[] = "\$this->addColumn('$tableName', '$unknownColumn', '$oldDbType');";
        }

        // TODO compare desired type with existing type

        if (empty($upCode) && empty($downCode)) {
            return ['',''];
        }

        return [
            implode("\n        ", $upCode),
            implode("\n        ", $downCode),
        ];
    }
}