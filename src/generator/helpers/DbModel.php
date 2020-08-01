<?php

namespace cebe\yii2openapi\generator\helpers;

use yii\base\BaseObject;

/**
 * @deprecated
 * Stores properties of a db model inferred from schema data.
 */
class DbModel extends BaseObject
{
    /**
     * @var string model name.
     */
    public $name;
    /**
     * @var string|null table name.
     */
    public $tableName;
    /**
     * @var string description from the schema.
     */
    public $description;
    /**
     * @var array model attributes.
     */
    public $attributes = [];
    /**
     * @var array database relations.
     */
    public $relations = [];
}
