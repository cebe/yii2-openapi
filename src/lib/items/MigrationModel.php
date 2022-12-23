<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;
use yii\helpers\{Inflector, VarDumper};
use cebe\yii2openapi\generator\ApiGenerator;
use function array_push;
use function array_unshift;
use function implode;
use function is_string;
use const PHP_EOL;

/**
 * @property-read string $tableAlias
 * @property-read string $upCodeString
 * @property-read string $downCodeString
 * @property-read string $fileClassName
 */
class MigrationModel extends BaseObject
{
    /**
     * @var string
     **/
    public $fileName;

    /**
     * @var array
     **/
    public $upCodes = [];

    /**
     * @var array
     **/
    public $downCodes = [];

    /**
     * @var array
     **/
    public $dependencies = [];

    /**
     * @var string
     **/
    private $fileClassName = '';

    /**
     * @var \cebe\yii2openapi\lib\items\DbModel
     */
    private $model;

    /**@var \cebe\yii2openapi\lib\items\ManyToManyRelation|null **/
    private $relation;

    public function __construct(DbModel $model, bool $isFresh = true, ManyToManyRelation $relation = null, $config = [])
    {
        parent::__construct($config);
        $this->model = $model;
        $this->relation = $relation;
        if ($relation === null) {
            $this->fileName = $isFresh
                ? 'create_table_' . $model->tableName
                : 'change_table_' . $model->tableName;
        } else {
            $this->fileName = $isFresh
                ? 'create_table_' . $relation->viaTableName
                : 'change_table_' . $relation->viaTableName;
        }
    }

    /**
     * @param ?array $codes
     * @param string $direction ENUM allowed value can be one of "UP", "DOWN"
     */
    public static function moveEnumStatementForPgsql(?array $codes, string $direction)
    {
        // for up migration move enum statemenet to botton
        // for down migration move enum statemenet to top

        if (!ApiGenerator::isPostgres() || empty($codes)) {
            return $codes;
        }

        // $enumKey = null;
        // foreach ($codes as $key => $code) {
        //     if (strpos($code, ' TYPE enum_') !== false) {
        //         $enumKey = $key;
        //         break;
        //     }
        // }

        // if ($enumKey !== null) {
        //     if ($direction === 'UP') {
        //         array_push($codes, $codes[$enumKey]);
        //         unset($codes[$enumKey]);
        //     } elseif ($direction === 'DOWN') {
        //         $code = $codes[$enumKey];
        //         unset($codes[$enumKey]);
        //         array_unshift($codes, $code);
        //     } else {
        //         throw new \Exception('Unknown direction found.');
        //     }
        // }
        return $codes;
    }

    public function getUpCodeString():string
    {
        return !empty($this->upCodes) ? implode(PHP_EOL, static::moveEnumStatementForPgsql($this->upCodes, 'UP')) : '';
    }

    public function getDownCodeString():string
    {
        return !empty($this->downCodes) ? implode(PHP_EOL, static::moveEnumStatementForPgsql($this->downCodes, 'DOWN')) : '';
    }

    public function notEmpty():bool
    {
        return !empty($this->upCodes) && !empty($this->downCodes);
    }

    public function getTableAlias():string
    {
        return $this->relation === null? $this->model->tableAlias : $this->relation->viaTableAlias;
    }

    public function getFileClassName():string
    {
        return $this->fileClassName;
    }

    public function getDescription():string
    {
        return $this->relation === null?
            'Table for '.$this->model->getClassName():
            'Table for '.$this->relation->getViaModelName();
    }

    public function makeClassNameByTime(int $index, ?string $nameSpace = null, ?string $date = null):string
    {
        if ($nameSpace) {
            $m = sprintf('%s%04d', ($date ?: date('ymdH')), $index);
            $this->fileClassName = "M{$m}" . Inflector::id2camel($this->fileName, '_');
        } else {
            $m = sprintf('%s%04d', ($date ?: date('ymd_H')), $index);
            $this->fileClassName = "m{$m}_" . $this->fileName;
        }
        return $this->fileClassName;
    }

    /**Add up code, by default at bottom
     * @param array|string $code
     * @param bool         $toTop
     * @return $this
     */
    public function addUpCode($code, bool $toTop = false):MigrationModel
    {
        $code = is_string($code) ? [$code] : $code;
        if ($toTop === true) {
            array_unshift($this->upCodes, ...$code);
        } else {
            array_push($this->upCodes, ...$code);
        }
        return $this;
    }

    /**add down code, by default to top
     * @param array|string $code
     * @param bool         $toBottom
     * @return $this
     */
    public function addDownCode($code, bool $toBottom = false):MigrationModel
    {
        $code = is_string($code) ? [$code] : $code;
        if ($toBottom === true) {
            array_push($this->downCodes, ...$code);
        } else {
            array_unshift($this->downCodes, ...$code);
        }
        return $this;
    }
}
