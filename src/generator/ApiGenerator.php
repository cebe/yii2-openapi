<?php

namespace cebe\yii2openapi\generator;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\PathItem;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Yii;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 *
 *
 * @author Carsten Brandt <mail@cebe.cc>
 */
class ApiGenerator extends Generator
{
    public $openApiPath;
    public $ignoreSpecErrors = false;
    public $generateUrls = true;
    public $generateControllers = true;
    public $generateModels = true;


    public $urlConfigFile = '@app/config/urls.rest.php';


    /**
     * @return string name of the code generator
     */
    public function getName()
    {
        return 'REST API Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator generates REST API code from an OpenAPI 3 specification.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['openApiPath', 'urlConfigFile'], 'filter', 'filter' => 'trim'],

            [['ignoreSpecErrors', 'generateUrls', 'generateModels', 'generateControllers'], 'boolean'],

            ['openApiPath', 'required'],
            ['openApiPath', 'validateSpec'],
            [['urlConfigFile'], 'required', 'when' => function(ApiGenerator $model) { return (bool) $model->generateUrls; }],


        ]);
    }

    public function validateSpec($attribute)
    {
        if ($this->ignoreSpecErrors) {
            return;
        }

        $openApi = $this->getOpenApi();
        if (!$openApi->validate()) {
            $this->addError($attribute, 'Failed to validate OpenAPI spec:' . Html::ul($openApi->getErrors()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'openApiPath' => 'OpenAPI 3 Spec file',
            'generateUrls' => 'Generate URL Rules',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(parent::hints(), [
            'openApiPath' => 'Path to the OpenAPI 3 Spec file. Type <code>@</code> to trigger autocomplete.',
            'urlConfigFile' => 'UrlRules will be written to this file.',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function autoCompleteData()
    {
        $vendor = Yii::getAlias('@vendor');
        $app = Yii::getAlias('@app');
        $paths = [];
        $pathIterator = new RecursiveDirectoryIterator($app);
        $recursiveIterator = new RecursiveIteratorIterator($pathIterator);
        $files = new RegexIterator($recursiveIterator, '~.+\.(json|yaml|yml)$~i', RegexIterator::GET_MATCH);
        foreach($files as $file) {
            if (strpos($file[0], $vendor) === 0) {
                $file = '@vendor' . substr($file[0], strlen($vendor));
                if (DIRECTORY_SEPARATOR === '\\') {
                    $file = str_replace('\\', '/', $file);
                }
            } elseif (strpos($file[0], $app) === 0) {
                $file = '@app' . substr($file[0], strlen($app));
                if (DIRECTORY_SEPARATOR === '\\') {
                    $file = str_replace('\\', '/', $file);
                }
            } else {
                $file = $file[0];
            }
            $paths[] = $file;
        }
        return [
            'openApiPath' => $paths,
//            'urlConfigFile' => [
//                '@app/config/urls.rest.php',
//            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        $required = [];
        if ($this->generateUrls) {
            $required[] = 'urls.php';
        }
        return $required;
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['generateUrls', 'urlConfigFile']);
    }



    /**
     * @var OpenApi
     */
    private $_openApi;


    /**
     * @return OpenApi
     */
    protected function getOpenApi()
    {
        if ($this->_openApi === null) {
            $file = Yii::getAlias($this->openApiPath);
            if (StringHelper::endsWith($this->openApiPath, '.json', false)) {
                $this->_openApi = Reader::readFromJsonFile($file);
            } else {
                $this->_openApi = Reader::readFromYamlFile($file);
            }
        }
        return $this->_openApi;
    }


    protected function generateUrls()
    {
        $openApi = $this->getOpenApi();

        $urlRules = [];
        foreach($openApi->paths as $path => $pathItem) {
            /** @var $pathItem PathItem */
            if ($path[0] !== '/') {
                throw new Exception('Path must begin with /');
            }

            $parts = explode('/', trim($path, '/'));

            $controller = [];
            $action = [];
            $params = false;
            foreach ($parts as $p => $part) {
                if (preg_match('/\{(.*)\}/', $part, $m)) {
                    $params = true;
                    $parts[$p] = '<' . $m[1] . '>';
                    // TODO add regex to param based on openAPI type
                } elseif ($params) {
                    $action[] = $part;
                } else {
                    $controller[] = Inflector::singularize($part);
                }
            }
            $pattern = implode('/', $parts);

            $controller = implode('-', $controller);
            if (empty($controller)) {
                $controller = 'default';
            }
            $action = empty($action) ? '' : '-' . implode('-', $action);
            foreach(array_keys($pathItem->getOperations()) as $method) {
                switch ($method) {
                    case 'get': $a = $params ? 'view' : 'index'; break;
                    case 'post': $a = 'create'; break;
                    case 'put': $a = 'update'; break;
                    case 'patch': $a = 'update'; break;
                    case 'delete': $a = 'delete'; break;
                    default: $a = "http-$method"; break;
                }
                $urlRules[strtoupper($method) . " $pattern"] = "$controller/$a$action";
            }
            // TODO add options action
        }
        return $urlRules;
    }

    protected function generateControllers()
    {
        $urls = $this->generateUrls();

        $c = [];
        foreach($urls as $route) {
            $parts = explode('/', $route);
            $c[$parts[0]][] = $parts[1];
        }
        return $c;
    }

    protected function generateModels()
    {
        $models = [];
        foreach($this->getOpenApi()->components->schemas as $schema) {
            // TODO implement
        }

        return $models;
    }


    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to [[\yii\gii\generators\controller\Generator::generate()]] as an example
     * on how to implement this method.
     * @return CodeFile[] a list of code files to be created.
     */
    public function generate()
    {
        $files = [];

        if ($this->generateUrls) {
            $files[] = new CodeFile(
                Yii::getAlias($this->urlConfigFile),
                $this->render('urls.php', [
                    'urls' => $this->generateUrls(),
                ])
            );
        }

        if ($this->generateControllers) {
            $controllers = $this->generateControllers();
            foreach($controllers as $controller => $actions) {
                $className = \yii\helpers\Inflector::id2camel($controller) . 'Controller';
                $files[] = new CodeFile(
                    Yii::getAlias(Yii::$app->controllerPath . "/$className.php"),
                    $this->render('controller.php', [
                        'className' => $className,
                        'namespace' => Yii::$app->controllerNamespace,
                        'actions' => $actions,
                    ])
                );
            }

        }

        if ($this->generateModels) {
            $models = $this->generateModels();
//            foreach($models as $modelName => $model) {
//                $className = \yii\helpers\Inflector::id2camel($modelName) . 'Controller';
//                $files[] = new CodeFile(
//                    Yii::getAlias("@app/models/$className.php"),
//                    $this->render('model.php', [
//                        'className' => $className,
//                        'namespace' => 'app\\models',
//                        'actions' => $actions,
//                    ])
//                );
//            }

        }

        return $files;
    }
}