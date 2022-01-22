<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\items;

class NonDbRelation
{
    public const HAS_ONE = 'hasOne';
    public const HAS_MANY = 'hasMany';

    /**
     * @var string $name
     **/
    private $name;

    /**
     * @var string $className
     **/
    private $className;

    /**
     * @var string $method (hasOne/hasMany)
     **/
    private $method;

    public function __construct(
        string $name,
        ?string $className = null,
        ?string $method = null
    ) {
        $this->name = $name;
        $this->className = $className;
        $this->method = $method;
    }

    /**
     * @param string $name
     * @return NonDbRelation
     */
    public function setName(string $name):NonDbRelation
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $className
     * @return NonDbRelation
     */
    public function setClassName(string $className):NonDbRelation
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @param string $method
     * @return NonDbRelation
     */
    public function setMethod(string $method):NonDbRelation
    {
        $this->method = $method;
        return $this;
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
    public function getClassName():?string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getMethod():?string
    {
        return $this->method;
    }

    public function isHasOne():bool
    {
        return $this->method === self::HAS_ONE;
    }
}
