<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\traits;

trait ForeignKeyConstraints
{
    /**
     * @var string
     * Contains foreign key constraint
     * @example 'SET NULL'
     * @example 'CASCADE'
     */
    public $onDeleteFkConstraint;

    /**
     * @var string
     * Contains foreign key constraint
     * @example 'SET NULL'
     * @example 'CASCADE'
     */
    public $onUpdateFkConstraint;
}
