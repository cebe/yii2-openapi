<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function array_key_exists;
use function array_map;
use function str_replace;

class JunctionSchemas
{
    public const PREFIX = 'junk_';

    /**@var array */
    private $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function byClassSchema(string $name):array
    {
        return $this->indexByClassSchema()[$name] ?? [];
    }

    public function byJunctionSchema(string $schemaName):array
    {
        return $this->indexByJunctionSchema()[$this->addPrefix($schemaName)] ?? [];
    }

    public function isJunctionSchema(string $schemaName):bool
    {
        return array_key_exists($this->addPrefix($schemaName), $this->indexByJunctionSchema());
    }

    public function hasMany2Many(string $schemaName):bool
    {
        return array_key_exists($schemaName, $this->indexByClassSchema());
    }

    public function isManyToManyProperty(string $schemaName, string $propertyName):bool
    {
        $otherCase = Inflector::singularize($propertyName);
        if ($otherCase === $propertyName) {
            $otherCase = Inflector::pluralize($propertyName);
        }
        return array_key_exists($propertyName, $this->byClassSchema($schemaName))
               || array_key_exists($otherCase, $this->byClassSchema($schemaName));
    }

    public function isJunctionRef(string $schemaName, string $propertyName):bool
    {
        return array_key_exists(
            $propertyName,
            ArrayHelper::index($this->data, 'refProperty', 'class')[$schemaName] ?? []
        );
    }

    public function isJunctionProperty(string $schemaName, string $propertyName):bool
    {
        return array_key_exists($propertyName, $this->byJunctionSchema($schemaName));
    }

    public function indexByClassSchema():array
    {
        return ArrayHelper::index($this->data, 'property', 'class');
    }

    public function indexByJunctionSchema():array
    {
        return ArrayHelper::index($this->data, 'property', 'junctionSchema');
    }

    public function indexByJunctionRef():array
    {
        return ArrayHelper::index($this->data, 'class', 'refProperty');
    }

    public function addPrefix(string $schemaName):string
    {
        return StringHelper::startsWith($schemaName, self::PREFIX) ? $schemaName : self::PREFIX . $schemaName;
    }

    public function trimPrefix(string $schemaName):string
    {
        return str_replace(self::PREFIX, '', $schemaName);
    }

    public function junctionCols(string $schemaName):array
    {
        return array_values(
            array_map(function (array $data) {
                return $data['property'] . '_id';
            }, $this->byJunctionSchema($schemaName))
        );
    }
}
