<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;

class UrlRule extends BaseObject
{
    /**@var string */
    public $path;

    /**@var string */
    public $method;

    /**@var string */
    public $pattern;

    /**@var string */
    public $route;

    /**@var int|string */
    public $idParam;

    /**@var array */
    public $actionParams = [];

    /**@var string */
    public $openApiOperation;

    /**@var string */
    public $modelClass;

    public $responseWrapper;
}
