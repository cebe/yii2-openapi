<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use function reset;

class AttributeRelation
{
    public $onUpdateFkConstraint;
    public $onDeleteFkConstraint;

    public const HAS_ONE = 'hasOne';
    public const HAS_MANY = 'hasMany';

    /**
     * @var string $name
     **/
    private $name;

    /**
     * @var string $tableName
     **/
    private $tableName;

    /**
     * @var string $className
     **/
    private $className;

    /**
     * @var string $method (hasOne/hasMany)
     **/
    private $method;

    /**
     * @var array
     **/
    private $link = [];

    /**@var  bool */
    private $selfReference = false;

    public function __construct(
        string $name,
        ?string $tableName = null,
        ?string $className = null,
        ?string $method = null,
        array $link = []
    ) {
        $this->name = $name;
        $this->tableName = $tableName;
        $this->className = $className;
        $this->method = $method;
        $this->link = $link;
    }

    /**
     * @param string $name
     * @return AttributeRelation
     */
    public function setName(string $name):AttributeRelation
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $tableName
     * @return AttributeRelation
     */
    public function setTableName(string $tableName):AttributeRelation
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @param string $className
     * @return AttributeRelation
     */
    public function setClassName(string $className):AttributeRelation
    {
        $this->className = $className;
        return $this;
    }

    public function asSelfReference():AttributeRelation
    {
        $this->selfReference = true;
        return $this;
    }

    public function asHasOne(array $link):AttributeRelation
    {
        $this->method = self::HAS_ONE;
        $this->link = $link;
        return $this;
    }

    public function asHasMany(array $link):AttributeRelation
    {
        $this->method = self::HAS_MANY;
        $this->link = $link;
        return $this;
    }

    public function isHasOne():bool
    {
        return $this->method === self::HAS_ONE;
    }

    public function isSelfReferenced():bool
    {
        return $this->selfReference;
    }

    /**
     * @return string
     */
    public function getName():string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTableName():string
    {
        return $this->tableName;
    }

    public function getTableAlias():string
    {
        return "{{%$this->tableName}}";
    }

    /**
     * @return string
     */
    public function getClassName():string
    {
        return $this->className;
    }

    public function getClassKey():string
    {
        return Inflector::camel2id($this->getClassName());
    }

    /**
     * @return string
     */
    public function getMethod():string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getLink():array
    {
        return $this->link;
    }

    public function getCamelName():string
    {
        return Inflector::camelize($this->name);
    }

    public function getColumnName():string
    {
        return reset($this->link);
    }

    public function getForeignName():string
    {
        return key($this->link);
    }

    public function linkToString():string
    {
        return str_replace(
            [',', '=>', ', ]'],
            [', ', ' => ', ']'],
            preg_replace('~\s+~', '', VarDumper::export($this->getLink()))
        );
    }
}
