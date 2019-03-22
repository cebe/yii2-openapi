<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\assets;

use yii\web\AssetBundle;

/**
 * This asset adds CSS style for bootstrap "card" component
 */
class BootstrapCardAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/css';
    public $css = [
        'bootstrap-card.css',
    ];
}
