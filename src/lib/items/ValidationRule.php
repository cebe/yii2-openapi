<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use function gettype;
use function implode;
use function is_string;
use function sprintf;

final class ValidationRule
{
    /**@var array * */
    public $attributes = [];

    /**@var string * */
    public $validator;

    /**@var array * */
    public $params = [];

    public function __construct(array $attributes, string $validator, array $params = [])
    {
        $this->attributes = array_values($attributes);
        $this->validator = $validator;
        $this->params = $params;
    }

    public function __toString():string
    {
        $attrs = implode("', '", $this->attributes);
        $params = empty($this->params) ? '' : ', ' . $this->arrayToString($this->params);
        return sprintf("[['%s'], '%s'%s]", $attrs, $this->validator, $params);
    }

    private function arrayToString(array $data):string
    {
        $params = [];
        foreach ($data as $key => $val) {
            $type = gettype($val);
            switch ($type) {
                case 'NULL':
                case 'boolean':
                    $value = VarDumper::export($val);
                    break;
                case 'integer':
                case 'float':
                case 'double':
                    $value = $val;
                    break;
                case 'array':
                    if (empty($val)) {
                        $value = '[]';
                    } elseif (ArrayHelper::isIndexed($val)) {
                        $value = "['" . implode("', '", $val) . "']";
                    } else {
                        $value = '[' . $this->arrayToString($val) . ']';
                    }
                    break;
                case 'resource':
                case 'object':
                    //probably will be resolved later
                    $value = "''";
                    break;
                default:
                    $value = "'$val'";
            }
            $params[] = is_string($key) ? "'$key' => $value" : $value;
        }
        return implode(', ', $params);
    }
}
