<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use yii\base\BaseObject;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function array_keys;
use function array_map;
use function implode;
use function strtr;
use function trim;
use function var_export;

/**
 * @property-read string      $actionMethodName
 * @property-read null|string $serializerConfig
 * @property-read null|array  $template
 * @property-read string      $parameterList
 * @property-read null|string $implementation
 * @property-read string      $baseModelName
 * @property-read string      $findModelMethodName
 * @property-read string      $optionsRoute
 * @property-read string      $route
 * @property-read array       $paramNames
 */
final class RestAction extends BaseObject
{
    /**@var string* */
    public $id;

    /**@var string */
    public $urlPath;

    /**@var string */
    public $urlPattern;

    /**@var string */
    public $requestMethod;

    /**@var string */
    public $controllerId;

    /**@var string|null */
    public $modelName;

    /**@var string|null */
    public $modelFqn;

    /**@var string|null */
    public $idParam;

    /**@var array */
    public $params = [];

    /**@var ?string */
    public $prefix;

    /**@var array */
    public $prefixSettings = [];
    /**
     * @var array|null
     */
    public $responseWrapper;

    public function getRoute():string
    {
        if ($this->prefix && !empty($this->prefixSettings)) {
            $prefix = $this->prefixSettings['module'] ?? $this->prefix;
            return trim($prefix, '/').'/'.$this->controllerId.'/'.$this->id;
        }
        return $this->controllerId.'/'.$this->id;
    }

    public function getOptionsRoute():string
    {
        //@TODO: re-check
        if ($this->prefix && !empty($this->prefixSettings)) {
            $prefix = $this->prefixSettings['module'] ?? $this->prefix;
            return trim($prefix, '/').'/'.$this->controllerId.'/options';
        }
        return $this->controllerId.'/options';
    }

    public function getBaseModelName():string
    {
        return $this->modelFqn ? StringHelper::basename($this->modelFqn) : '';
    }

    public function getParamNames():array
    {
        return array_keys($this->params);
    }

    public function getParameterList():string
    {
        return implode(', ', array_map(static function ($p) {
            return "\$$p";
        }, $this->getParamNames()));
    }

    public function getActionMethodName():string
    {
        return 'action' . Inflector::id2camel($this->id);
    }

    public function getFindModelMethodName():string
    {
        return 'find' . $this->baseModelName . 'Model';
    }

    public function hasStandardId():bool
    {
        return $this->idParam === null || $this->idParam === 'id';
    }

    public function hasTemplate():bool
    {
        return ActionTemplates::hasTemplate($this->id);
    }

    public function getTemplate():?string
    {
        //@TODO: Model scenarios for create/update actions
        $template = ActionTemplates::getTemplate($this->id);
        if (!$template) {
            return null;
        }

        return <<<"PHP"
'{$this->id}' => [
                'class' => {$template['class']},
                'modelClass' => \\{$this->modelFqn}::class,
                'checkAccess' => [\$this, 'checkAccess'],
            ]
PHP;
    }

    public function getSerializerConfig():?string
    {
        if (empty($this->responseWrapper) || !$this->hasTemplate()) {
            return null;
        }
        if (!empty($this->responseWrapper['item'])) {
            $tpl = '        if ($action->id === {actionId}) {' . "\n"
                . '            return [{wrapItem} => $serializer->serialize($result)];' . "\n"
                . '        }';
            return strtr(
                $tpl,
                [
                    '{actionId}' => var_export($this->id, true),
                    '{wrapItem}' => var_export($this->responseWrapper['item'], true),
                ]
            );
        }

        if (!empty($this->responseWrapper['list'])) {
            $tpl = '        if ($action->id === {actionId}) {' . "\n"
                . '            $serializer->collectionEnvelope = {wrapList};' . "\n"
                . '            return $serializer->serialize($result);' . "\n"
                . '        }';
            return strtr(
                $tpl,
                [
                    '{actionId}' => var_export($this->id, true),
                    '{wrapList}' => var_export($this->responseWrapper['list'], true),
                ]
            );
        }
        return null;
    }

    public function shouldBeAbstract(): bool
    {
        if (!$this->modelName || !$this->hasTemplate()) {
            return true;
        }

        if ($this->hasStandardId()) {
            return false; //Default template action used
        }

        if (!ActionTemplates::hasImplementation($this->id)) {
            return true;
        }

        return false;
    }

    public function getImplementation():?string
    {
        $template = ActionTemplates::getTemplate($this->id);
        return strtr(
            $template['implementation'],
            [
                'findModel' => $this->getFindModelMethodName(),
                '$id' => '$' . $this->idParam,
                'ACTION_ID' => var_export($this->id, true),
            ]
        );
    }

    public function shouldUseTemplate():bool
    {
        return isset($this->modelFqn) && $this->hasTemplate() && $this->hasStandardId();
    }

    public function shouldUseCustomFindModel():bool
    {
        return ActionTemplates::hasImplementation($this->id) && !$this->hasStandardId();
    }
}
