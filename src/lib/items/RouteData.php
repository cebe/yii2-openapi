<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

use cebe\openapi\spec\PathItem;
use yii\base\BaseObject;
use yii\base\InvalidCallException;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use function array_filter;
use function count;
use function in_array;
use function preg_match;
use function reset;
use function trim;

/**
 * @property-read array        $partsWithoutParams
 * @property-read array        $firstParam
 * @property-read array        $parts
 * @property-read string       $lastPart
 * @property-read  string      $path
 * @property-read  string      $type
 * @property-read  string      $pattern
 * @property-read  string      $controller
 * @property-read  string      $action
 * @property-read  string|null $idParam
 * @property-read  string|null $parentParam
 * @property-read  array       $params
 */
final class RouteData extends BaseObject
{
    private const PATTERN_PROFILE = '~^/(?<controller>me)$~';
    /**
     * @example /auth/login
     * @example /auth/reset/password
     * @example /auth/verify/{email}
     */
    private const PATTERN_ACTION = '~^/(?<controller>[\w-]+)/(?<action>[\w-]+)/?({(?<param>[\w-]+)}/)?(?<method>[\w-]+)?/?(?:[{}/\w-]+)?$~';
    /** @example /posts */
    private const PATTERN_LIST = '~^/(?<controller>[\w-]+)$~';
    /**
     * @example /posts/1
     * @example /article/4
     */
    private const PATTERN_RESOURCE = '~^/(?<controller>[\w-]+)/{(?<idParam>[\w-]+)}$~';
    /** @example /posts/1/upload/avatar */
    /** @example /users/1/generate/password */
    private const PATTERN_RESOURCE_OPERATION
        = '~^/(?<controller>[\w-]+)/{(?<idParam>[\w-]+)}/(?<action>(?!relationships)[\w-]+)/(?<method>[\w-]+)$~';
    /**
     * @example /posts/1/comments
     * @example /article/4/category
     */
    private const PATTERN_LIST_FOR_RESOURCE = '~^/(?<for>[\w-]+)/{(?<parentParam>[\w-]+)}/(?<controller>[\w-]+)$~';
    /** @example /posts/1/comments/33 */
    private const PATTERN_FOR_RESOURCE = '~^/(?<for>[\w-]+)/{(?<parentParam>[\w-]+)}/(?<controller>[\w-]+)/{(?<idParam>[\w-]+)}$~';
    /** @example /posts/1/relationships/author */
    private const PATTERN_RELATIONSHIP = '~^/(?<controller>[\w-]+)/{(?<idParam>[\w-]+)}/relationships/(?<relation>[\w-]+)$~';
    private const PATTERN_PARAM = '~{(.+?)}~';

    public const TYPE_DEFAULT = 'default';
    public const TYPE_PROFILE = 'profile';
    public const TYPE_COLLECTION = 'collection';
    public const TYPE_COLLECTION_FOR = 'collection_for';
    public const TYPE_RESOURCE = 'resource';
    public const TYPE_RESOURCE_FOR = 'resource_for';
    public const TYPE_RESOURCE_OPERATION = 'resource_operation';
    public const TYPE_RELATIONSHIP = 'relationship';

    private static $patternMap = [
        self::TYPE_PROFILE => self::PATTERN_PROFILE,
        self::TYPE_DEFAULT => self::PATTERN_ACTION,
        self::TYPE_COLLECTION => self::PATTERN_LIST,
        self::TYPE_RESOURCE => self::PATTERN_RESOURCE,
        self::TYPE_RESOURCE_OPERATION => self::PATTERN_RESOURCE_OPERATION,
        self::TYPE_COLLECTION_FOR => self::PATTERN_LIST_FOR_RESOURCE,
        self::TYPE_RESOURCE_FOR => self::PATTERN_FOR_RESOURCE,
        self::TYPE_RELATIONSHIP => self::PATTERN_RELATIONSHIP,
    ];

    /**
     * @var string
     **/
    private $relatedModel;

    /**
     * @var string
     **/
    private $type = 'default';

    /**
     * @var string
     **/
    private $pattern;

    /**
     * @var string
     **/
    private $action = '';

    /**
     * @var string
     **/
    private $controller;

    /**
     * @var string|null
     **/
    private $idParam;

    /**
     * @var string|null
     **/
    private $parentParam;

    /**
     * @var array
     **/
    private $params = [];

    /**
     * @var bool
     **/
    private $hasParams = false;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $unprefixedPath;

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var array
     **/
    private $prefixSettings = [];

    /**
     * @var array
     */
    private $parts;

    /**
     * @var PathItem $pathItem
     */
    private $pathItem;

    /**
     * @var array
     */
    private $urlPrefixes;

    public function __construct(PathItem $pathItem, string $path, array $urlPrefixes = [], $config = [])
    {
        $this->path = $this->unprefixedPath = $path;
        $this->parts = explode('/', trim($path, '/'));
        $this->pathItem = $pathItem;
        $this->urlPrefixes = $urlPrefixes;

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
        $this->detectUrlPattern();

        $patternParts = $this->parts;
        $pathParameters = ArrayHelper::index($this->pathItem->parameters, 'name');

        foreach ($this->parts as $p => $part) {
            if ($part === 'relationships' || !preg_match(self::PATTERN_PARAM, $part, $m)) {
                continue;
            }
            $this->hasParams = true;
            $paramName = $m[1];
            if (array_key_exists($paramName, $pathParameters)) {
                //$additional = $pathParameters[$paramName]->schema->additionalProperties ?? null;
                $this->params[$paramName] = [
                    //@TODO: use only required params
                    //'required'=> $pathParameters[$paramName]->required,
                    'type' => $pathParameters[$paramName]->schema->type ?? null,
                    //'model' => $additional ? SchemaResponseResolver::guessModelByRef($additional) : null,
                ];
            } else {
                $this->params[$paramName] = null;
            }
            $type = $this->params[$paramName]['type'] ?? null;
            //check minimum/maximum for routes like <year:\d{4}> ?
            if ($type === 'integer') {
                $patternParts[$p] = '<' . $paramName . ':\d+>';
            } elseif ($type === 'string') {
                $patternParts[$p] = '<' . $paramName . ':[\w-]+>';
            } else {
                $patternParts[$p] = '<' . $paramName . '>';
            }
        }
        $this->pattern = implode('/', $patternParts);
        if ($this->hasParams && $this->isRelationship()) {
            $this->relatedModel = $this->getFirstParam()['model'] ?? null;
        }
    }

    protected function detectUrlPattern():void
    {
        if ($this->path === '/') {
            $this->type = self::TYPE_DEFAULT;
            $this->action = '';
            $this->controller = 'default';
            return;
        }
        foreach ($this->urlPrefixes as $prefix => $rule) {
            if (!str_starts_with($this->path, $prefix)) {
                continue;
            }
            $this->prefix = $prefix;
            $this->unprefixedPath = '/' . trim(str_replace($prefix, '', $this->path), '/');
            $this->parts = explode('/', trim($this->unprefixedPath, '/'));
            $this->prefixSettings = is_array($rule) ? $rule : [];
        }
        foreach (self::$patternMap as $type => $pattern) {
            if (preg_match($pattern, $this->path, $matches)) {
                $this->type = $type;
                break;
            }
        }
        if (!isset($matches)) {
            throw new InvalidCallException('Unrecognized path pattern');
        }
        $this->controller = Inflector::camel2id(Inflector::singularize($matches['controller'] ?? ''));
        switch ($this->type) {
            case self::TYPE_RESOURCE:
                $this->action = '';
                $this->idParam = $matches['idParam'];
                break;
            case self::TYPE_COLLECTION:
                $this->action = '';
                break;
            case self::TYPE_RESOURCE_OPERATION:
                $this->idParam = $matches['idParam'];
                $this->action = Inflector::camel2id($matches['action']) . '-' . Inflector::camel2id($matches['method']);
                break;
            case self::TYPE_COLLECTION_FOR:
                $this->action = '-for-' . Inflector::camel2id(Inflector::singularize($matches['for']));
                $this->parentParam = $matches['parentParam'];
                break;
            case self::TYPE_RESOURCE_FOR:
                $this->parentParam = $matches['parentParam'];
                $this->idParam = $matches['idParam'];
                $this->action = '-for-' . Inflector::camel2id(Inflector::singularize($matches['for']));
                break;
            case self::TYPE_RELATIONSHIP:
                $this->idParam = $matches['idParam'];
                $this->action = '-related-' . Inflector::camel2id($matches['relation']);
                break;
            default:
                $this->action = Inflector::camel2id($matches['action'] ?? '');
                if (isset($matches['method'])) {
                    $this->action .= '-' . Inflector::camel2id($matches['method']);
                }
                if (!$this->controller) {
                    $this->controller = 'default';
                }
        }
    }

    public function getType():string
    {
        return $this->type;
    }

    public function getPattern():string
    {
        return $this->pattern;
    }

    public function getAction():string
    {
        return $this->action;
    }

    public function getController():?string
    {
        return $this->controller;
    }

    public function getIdParam():?string
    {
        return $this->idParam;
    }

    public function getParentParam():?string
    {
        return $this->parentParam;
    }

    public function getParams():array
    {
        return $this->params;
    }

    public function getPath():string
    {
        return $this->path;
    }

    public function getParts():array
    {
        return $this->parts;
    }

    public function getRelatedModel():?string
    {
        return $this->relatedModel;
    }

    public function isRelationship():bool
    {
        return $this->type === self::TYPE_RELATIONSHIP;
    }

    public function resolveGetActionType():string
    {
        if ($this->type === self::TYPE_DEFAULT) {
            return '';
        }
        if ($this->type === self::TYPE_PROFILE || $this->isNonCrudAction()) {
            return 'view';
        }
        if ($this->hasParams && $this->isLastPartIsParam()) {
            return 'view';
        }
        if ($this->hasParams && $this->isRelationship()) {
            return $this->isSingularAction() ? 'view' : 'list';
        }
        return 'list';
    }

    private function getLastPart():string
    {
        return $this->parts[count($this->parts) - 1];
    }

    private function isLastPartIsParam():bool
    {
        return preg_match(self::PATTERN_PARAM, $this->getLastPart());
    }

    private function getPartsWithoutParams():array
    {
        return array_filter(
            $this->parts,
            function ($part) {
                return !preg_match(self::PATTERN_PARAM, $part);
            }
        );
    }

    private function getFirstParam():array
    {
        return reset($this->params);
    }

    private function isSingularAction():bool
    {
        return $this->action && Inflector::singularize($this->action) === $this->action;
    }

    public function isModelBasedType():bool
    {
        return !in_array($this->type, [self::TYPE_DEFAULT, self::TYPE_PROFILE]);
    }

    public function isNonCrudAction():bool
    {
        return in_array($this->type, [self::TYPE_DEFAULT, self::TYPE_RESOURCE_OPERATION]);
    }
}
