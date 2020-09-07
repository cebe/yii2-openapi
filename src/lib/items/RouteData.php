<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;

/**
 * @property-read string $route
 * @property-read string $optionsRoute
 */
class RouteData extends BaseObject
{
    /**@var string */
    public $path;

    /**@var string */
    public $method;

    /**@var string */
    public $pattern;

    /**@var string */
    public $controllerId;

    /**@var string */
    public $actionId;

    /**@var int|string */
    public $idParam;

    /**@var array */
    public $actionParams = [];

    /**@var string */
    public $modelClass;

    /**
     * @var array|null
     */
    public $responseWrapper;

    public function getRoute():string
    {
        return $this->controllerId.'/'.$this->actionId;
    }

    public function getOptionsRoute():string
    {
        return $this->controllerId.'/options';
    }
}
