<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib\generators;

use cebe\yii2openapi\lib\CodeFiles;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\items\FractalAction;
use cebe\yii2openapi\lib\items\RestAction;
use Laminas\Code\Generator\AbstractMemberGenerator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\ValueGenerator;
use Yii;
use yii\gii\CodeFile;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

class ControllersGenerator
{
    /**
     * @var \cebe\yii2openapi\lib\Config
     */
    protected $config;

    /**
     * @var array|\cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction[]
     */
    protected $controllers;

    protected $files;

    public function __construct(Config $config, array $actions = [])
    {
        $this->config = $config;
        $this->controllers = ArrayHelper::index($actions, null, 'controllerId');
        $this->files = new CodeFiles([]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function generate():CodeFiles
    {
        if (!$this->config->generateControllers) {
            return new CodeFiles([]);
        }
        $namespace = $this->config->controllerNamespace ?? Yii::$app->controllerNamespace;
        $path = $this->config->getPathFromNamespace($namespace);
        $templateName = $this->config->useJsonApi ? 'controller_jsonapi.php' : 'controller.php';

        foreach ($this->controllers as $controller => $actions) {
            $controllerNamespace = $namespace;
            $controllerPath = $path;
            /**
             * @var RestAction|FractalAction $action
            **/
            $action = $actions[0];
            if ($action->prefix && !empty($action->prefixSettings)) {
                $controllerNamespace = trim($action->prefixSettings['namespace'], '\\');
                $controllerPath = $action->prefixSettings['path']
                    ?? $this->config->getPathFromNamespace($controllerNamespace);
            }
            $className = Inflector::id2camel($controller) . 'Controller';
            $this->files->add(new CodeFile(
                Yii::getAlias($controllerPath . "/base/$className.php"),
                $this->config->render(
                    $templateName,
                    [
                        'className' => $className,
                        'namespace' => $controllerNamespace . '\\base',
                        'actions' => $actions,
                    ]
                )
            ));
            // only generate custom classes if they do not exist, do not override
            if (!file_exists(Yii::getAlias("$controllerPath/$className.php"))) {
                $classFileGenerator = $this->makeCustomController($className, $controllerNamespace, $actions);
                $this->files->add(new CodeFile(
                    Yii::getAlias("$controllerPath/$className.php"),
                    $classFileGenerator->generate()
                ));
            }
        }
        return $this->files;
    }

    /**
     * @param string $className
     * @param string $controllerNamespace
     * @param RestAction[]|FractalAction[] $actions
     * @return FileGenerator
     */
    protected function makeCustomController(
        string $className,
        string $controllerNamespace,
        array $actions
    ):FileGenerator {
        $classFileGenerator = new FileGenerator();
        $reflection = new ClassGenerator(
            $className,
            $controllerNamespace,
            null,
            $controllerNamespace . '\\base\\' . $className
        );
        /**@var FractalAction[]|RestAction[] $abstractActions * */
        $abstractActions = array_filter($actions, static function ($action) {
            return $action->shouldBeAbstract();
        });
        if ($this->config->useJsonApi) {
            $body = <<<'PHP'
$actions = parent::actions();
return $actions;
PHP;
            $reflection->addMethod('actions', [], AbstractMemberGenerator::FLAG_PUBLIC, $body);
        }
        $params = [
            new ParameterGenerator('action'),
            new ParameterGenerator('model', null, new ValueGenerator(null)),
            new ParameterGenerator('params', null, new ValueGenerator([])),
        ];
        $reflection->addMethod('checkAccess', $params, AbstractMemberGenerator::FLAG_PUBLIC, '//TODO implement checkAccess');
        foreach ($abstractActions as $action) {
            $params = array_map(static function ($param) {
                return ['name' => $param];
            }, $action->getParamNames());
            $reflection->addMethod(
                $action->actionMethodName,
                $params,
                AbstractMemberGenerator::FLAG_PUBLIC,
                '//TODO implement ' . $action->actionMethodName
            );
        }
        $classFileGenerator->setClasses([$reflection]);
        return $classFileGenerator;
    }
}
