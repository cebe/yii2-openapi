<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;
use yii\helpers\Inflector;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;

/**
 * @property-read string                                                 $name
 * @property-read array                                                  $defaultRelations
 * @property-read string                                                 $modelFQN
 * @property-read string                                                 $fQN
 * @property-read array|\cebe\yii2openapi\lib\items\AttributeRelation[]  $relations
 * @property-read \cebe\yii2openapi\lib\items\ManyToManyRelation[]|array $many2Many
 * @property-read array                                                  $availableRelations
 */
class Transformer extends BaseObject
{
    /**@var \cebe\yii2openapi\lib\items\DbModel**/
    public $dbModel;
    /**
     * @var bool
     */
    private $singularResourceKey;
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $modelNamespace;

    public function __construct(
        DbModel $dbModel,
        string $namespace,
        string $modelNamespace,
        bool $singularResourceKey = false
    ) {
        $this->dbModel = $dbModel;
        $this->namespace = $namespace;
        $this->modelNamespace = $modelNamespace;
        $this->singularResourceKey = $singularResourceKey;
        parent::__construct([]);
    }

    public function getName(): string
    {
        return $this->dbModel->getClassName().'Transformer';
    }

    public function getFQN(): string
    {
        return $this->namespace.'\\'.$this->getName();
    }

    public function getModelFQN(): string
    {
        return $this->modelNamespace.'\\'.$this->dbModel->getClassName();
    }

    public function shouldIncludeRelations(): bool
    {
        return !empty($this->availableRelations);
    }

    public function getUniqueTransformerClasses(): array
    {
        $dbRelations = array_filter($this->dbModel->relations, function (AttributeRelation $rel) {
            return $rel->getClassName() !== $this->dbModel->getClassName();
        });
        $relations = array_map(static function (AttributeRelation $relation) {
            return Inflector::singularize($relation->getClassName()).'Transformer';
        }, $dbRelations);

        $relationsMany = array_map(static function (ManyToManyRelation $relation) {
            return Inflector::singularize($relation->getRelatedClassName()).'Transformer';
        }, $this->dbModel->many2many);

        return array_unique(array_merge($relations, $relationsMany));
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\AttributeRelation[]
     */
    public function getRelations(): array
    {
        return $this->dbModel->relations;
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\ManyToManyRelation[]
     */
    public function getMany2Many(): array
    {
        return $this->dbModel->many2many;
    }

    public function getDefaultRelations(): array
    {
        return [];
    }

    public function getAvailableRelations(): array
    {
        $relations = array_map(static function (AttributeRelation $relation) {
            return Inflector::variablize($relation->getName());
        }, $this->dbModel->relations);

        $relationsMany = array_map(static function (ManyToManyRelation $relation) {
            return Inflector::variablize($relation->name);
        }, $this->dbModel->many2many);

        return array_merge($relations, $relationsMany);
    }

    public function makeResourceKey(string $value): string
    {
        $value = $this->singularResourceKey ? Inflector::singularize($value): Inflector::pluralize($value);
        return strtolower(Inflector::camel2id($value, '_'));
    }
}
