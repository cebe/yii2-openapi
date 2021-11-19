<?php

namespace cebe\yii2openapi\lib\generators;

use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\CodeFiles;
use Yii;
use yii\gii\CodeFile;

class UrlRulesGenerator
{
    /**
     * @var \cebe\yii2openapi\lib\Config
     */
    protected $config;

    /**
     * @var array|\cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction[]
     */
    protected $actions;

    public function __construct(Config $config, array $actions = []) {

        $this->config = $config;
        $this->actions = $actions;
    }
    public function generate():CodeFiles
    {
        if (!$this->config->generateUrls) {
            return new CodeFiles([]);
        }

        $urls = [];
        $optionsUrls = [];
        foreach ($this->actions as $action) {
            $urls["{$action->requestMethod} {$action->urlPattern}"] = $action->route;
            $optionsUrls[$action->urlPattern] = $action->getOptionsRoute();
        }
        $urls = array_merge($urls, $optionsUrls);
        $file = new CodeFile(
            Yii::getAlias($this->config->urlConfigFile),
            $this->config->render('urls.php', ['urls' => $urls])
        );
        return new CodeFiles([$file]);
    }
}