<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\generator;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\Config;
use cebe\yii2openapi\lib\generators\ControllersGenerator;
use cebe\yii2openapi\lib\generators\JsonActionGenerator;
use cebe\yii2openapi\lib\generators\MigrationsGenerator;
use cebe\yii2openapi\lib\generators\ModelsGenerator;
use cebe\yii2openapi\lib\generators\RestActionGenerator;
use cebe\yii2openapi\lib\generators\TransformersGenerator;
use cebe\yii2openapi\lib\generators\UrlRulesGenerator;
use cebe\yii2openapi\lib\PathAutoCompletion;
use cebe\yii2openapi\lib\SchemaToDatabase;
use Yii;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use function array_merge;
use function get_object_vars;
use function in_array;
use function is_array;

class ApiGenerator extends Generator
{
    /**
     * @var string path to the OpenAPI specification file. This can be an absolute path or a Yii path alias.
     */
    public $openApiPath;

    /**
     * @var bool this flag controls whether files should be generated even if the spec contains errors.
     * If this is true, the spec will not be validated. Defaults to false.
     */
    public $ignoreSpecErrors = false;

    /**
     * @var bool whether to generate URL rules for Yii UrlManager from the API spec.
     */
    public $generateUrls = true;

    /**
     * @var array Special url prefixes
     * @example
     * 'urlPrefixes' => [
     * //Prefix will be ignored in url pattern,
     * //Rule like ['/calendar/<controller>/<action>' => '<controller>/<action>']
     *    'calendar' => '',
     * //Controller for url with this prefix will be located directly at defined path and namespace
     * //Rule like ['/api/v1/<controller>/<action>' => '/api/v1/<controller>/<action>']
     *    'api/v1/' => ['path' => '@app/modules/api/controllers/v1/', 'namespace' => '\app\modules\api\v1'],
     * //Controller for url with this prefix will be located directly at defined namespace, path resolved by namespace
     * //Rule like ['/prefix/<controller>/<action>' => '/xxx/<controller>/<action>']
     *    'prefix' => ['module' => 'xxx','namespace' => '\app\modules\xxx\controllers']
     * ]
     * Note: Order may be important! define most detailed prefixes before less detailed if you want different
     * prefixes with common part, for. ex 'user/auth' should be declared before 'user'
     **/
    public $urlPrefixes = [];

    /**
     * @var string file name for URL rules.
     */
    public $urlConfigFile = '@app/config/urls.rest.php';

    /**
     * @var bool whether to generate Controllers from the spec.
     */
    public $generateControllers = true;

    /**
     * @var bool use actions that return responses by JsonApi spec instead of default yii rest
     */
    public $useJsonApi = false;

    /**
     * @var bool if true, transformers will be generated in base subdirectory and overridable classes will extend it
     */
    public $extendableTransformers = true;

    /**
     * @var bool if true singular resource keys will be used /post/{id}, plural by default
     */
    public $singularResourceKeys = false;

    /**
     * @var string namespace to create controllers in. This must be resolvable via Yii alias.
     * Defaults to `null` which means to use the application controller namespace: `Yii::$app->controllerNamespace`.
     */
    public $controllerNamespace;

    /**
     * @var bool whether to generate ActiveRecord models from the spec.
     */
    public $generateModels = true;

    /**
     * @var bool whether to generate Faker for generating dummy data for each model.
     */
    public $generateModelFaker = true;

    /**
     * @var bool namespace to create models in. This must be resolvable via Yii alias.
     * Defaults to `app\models`.
     */
    public $modelNamespace = 'app\\models';

    /**
     * @var bool namespace to create fake data generators in. This must be resolvable via Yii alias.
     * Defaults to `app\models`.
     */
    public $fakerNamespace = 'app\\models';

    /**
     * @var string namespace to create fractal transformers in. (Only when generatedControllers and useJsonApi checked)
     * Defaults to `app\transformers`.
     */
    public $transformerNamespace = 'app\\transformers';

    /**
     * @var array List of model names to exclude.
     */
    public $excludeModels = [];

    /**
     * @var array Map for custom controller names not based on model name for exclusive cases
     * @example
     *  'controllerModelMap' => [
     *      'User' => 'Profile',  //use ProfileController for User model
     *      'File' => 'Upload',   //use UploadController for File model
     *  ]
     **/
    public $controllerModelMap = [];

    /**
     * @var bool Generate database models only for Schemas that not starts with underscore
     */
    public $skipUnderscoredSchemas = true;

    /**
     * @var bool Generate database models only for Schemas that have the `x-table` annotation.
     */
    public $generateModelsOnlyXTable = false;

    /**
     * @var bool whether to generate database migrations.
     */
    public $generateMigrations = true;

    /**
     * @var string path to create migration files in.
     * Defaults to `@app/migrations`.
     */
    public $migrationPath = '@app/migrations';

    /**
     * @var string namespace to create migrations in.
     * Defaults to `null` which means that migrations are generated without namespace.
     */
    public $migrationNamespace;

    /**
     * @var OpenApi
     */
    private $_openApiWithoutRef;

    /**
     * @var \cebe\yii2openapi\lib\Config $config
     **/
    private $config;

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
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'openApiPath',
                        'urlConfigFile',
                        'controllerNamespace',
                        'modelNamespace',
                        'fakerNamespace',
                        'migrationPath',
                        'migrationNamespace',
                        'transformerNamespace',
                    ],
                    'filter',
                    'filter' => function ($value) {
                        return $value !== null ? trim($value) : $value;
                    },
                ],

                [['controllerNamespace', 'migrationNamespace'], 'default', 'value' => null],

                [
                    [
                        'ignoreSpecErrors',
                        'generateUrls',
                        'generateModels',
                        'generateModelFaker',
                        'generateControllers',
                        'generateModelsOnlyXTable',
                        'skipUnderscoredSchemas',
                        'useJsonApi',
                        'extendableTransformers',
                        'singularResourceKeys',
                    ],
                    'boolean',
                ],

                ['openApiPath', 'required'],
                ['openApiPath', 'validateSpec'],
                ['urlPrefixes', 'validateUrlPrefixes'],
                [
                    ['urlConfigFile'],
                    'required',
                    'when' => function (ApiGenerator $model) {
                        return (bool)$model->generateUrls;
                    },
                ],
                [
                    ['modelNamespace'],
                    'required',
                    'when' => function (ApiGenerator $model) {
                        return (bool)$model->generateModels;
                    },
                ],
                [
                    ['fakerNamespace'],
                    'required',
                    'when' => function (ApiGenerator $model) {
                        return (bool)$model->generateModelFaker;
                    },
                ],
                [
                    ['migrationPath'],
                    'required',
                    'when' => function (ApiGenerator $model) {
                        return (bool)$model->generateMigrations;
                    },
                ],
                [
                    ['transformerNamespace'],
                    'required',
                    'when' => function (ApiGenerator $model) {
                        return (bool)$model->generateControllers && (bool)$model->useJsonApi;
                    },
                ],
            ]
        );
    }

    /**
     * @param $attribute
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    public function validateSpec($attribute):void
    {
        if ($this->ignoreSpecErrors) {
            return;
        }
        $config = $this->makeConfig();
        $openApi = $this->getOpenApiWithoutReferences();
        if (!$openApi->validate()) {
            $this->addError($attribute, 'Failed to validate OpenAPI spec:' . Html::ul($openApi->getErrors()));
        }
    }

    public function validateUrlPrefixes($attribute):void
    {
        if (empty($this->urlPrefixes)) {
            return;
        }
        foreach ($this->urlPrefixes as $prefix => $rule) {
            if (in_array(trim($prefix), ['', '/'])) {
                $this->addError($attribute, 'Root prefix not allowed, use controllerNamespace settings for it');
                return;
            }
            if (!is_array($rule) && !empty($rule)) {
                $this->addError(
                    $attribute,
                    'Invalid definition for prefix "' . $prefix
                    . '" it should be empty for ignore, or array with keys "path", "namespace", "module"'
                );
                return;
            }
            if (is_array($rule) && !isset($rule['namespace'])) {
                $this->addError(
                    $attribute,
                    'Invalid definition for prefix "' . $prefix . 'at least "namespace" required'
                );
                return;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
                'openApiPath' => 'OpenAPI 3 Spec file',
                'generateUrls' => 'Generate URL Rules',
                'generateModelsOnlyXTable' => 'Generate DB Models and Tables only for schemas that include `x-table` property',
                'skipUnderscoredSchemas' => 'Generate DB Models and Tables only for schemas that not starts with underscore',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return array_merge(
            parent::hints(),
            [
                'openApiPath' => 'Path to the OpenAPI 3 Spec file. Type <code>@</code> to trigger autocomplete.',
                'urlConfigFile' => 'UrlRules will be written to this file.',
                'controllerNamespace' => 'Namespace to create controllers in. This must be resolvable via Yii alias. Default is the application controller namespace: <code>Yii::$app->controllerNamespace</code>.',
                'modelNamespace' => 'Namespace to create models in. This must be resolvable via Yii alias.',
                'fakerNamespace' => 'Namespace to create fake data generators in. This must be resolvable via Yii alias.',
                'migrationPath' => 'Path to create migration files in.',
                'migrationNamespace' => 'Namespace to create migrations in. If this is empty, migrations are generated without namespace.',
                'generateModelFaker' => 'Generate Faker for generating dummy data for each model.',
                'useJsonApi' => 'use actions that return responses followed JsonApi specification',
                'singularResourceKeys' => 'Use singular resource keys (/post/{id}) (Plural by default : /posts/{id})',
                'transformerNamespace' => 'Namespace to create fractal transformers',
                'extendableTransformers' => 'If checked, transformers will be generate in base subdirectory and overridable classes will extend it, otherwise it will be autogenerated only',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function autoCompleteData()
    {
        return (new PathAutoCompletion())->complete();
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
        if ($this->generateControllers) {
            if ($this->useJsonApi) {
                $required[] = 'controller_jsonapi.php';
                $required[] = 'transformer.php';
            }
            $required[] = 'controller.php';
        }
        if ($this->generateModels) {
            $required[] = 'dbmodel.php';
        }
        if ($this->generateModelsOnlyXTable) {
            $required[] = 'model.php';
        }
        if ($this->generateModelFaker) {
            $required[] = 'basefaker.php';
            $required[] = 'faker.php';
        }
        if ($this->generateMigrations) {
            $required[] = 'migration.php';
        }
        return $required;
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(
            parent::stickyAttributes(),
            [
                'generateUrls',
                'urlConfigFile',
                'controllerNamespace',
                'modelNamespace',
                'fakerNamespace',
                'migrationPath',
                'migrationNamespace',
            ]
        );
    }

    public function makeConfig():Config
    {
        if (!$this->config) {
            $props = get_object_vars($this);
            $excludeProps = [
                'ignoreSpecErrors',
                'config',
                'template',
                'enableI18N',
                'messageCategory',
                'templates',
                '_openApiWithoutRef',
            ];
            foreach ($excludeProps as $key) {
                unset($props[$key]);
            }
            $this->config = new Config($props);
            $this->config->setFileRenderer(function ($template, $params) {
                return $this->render($template, $params);
            });
        }
        return $this->config;
    }

    /**
     * Generates the code based on the current user input and the specified code template files.
     * This is the main method that child classes should implement.
     * Please refer to [[\yii\gii\generators\controller\Generator::generate()]] as an example
     * on how to implement this method.
     * @return CodeFile[] a list of code files to be created.
     * @throws \Exception
     */
    public function generate():array
    {
        $config = $this->makeConfig();
        $actionsGenerator = $this->useJsonApi
            ? Yii::createObject(JsonActionGenerator::class, [$config])
            : Yii::createObject(RestActionGenerator::class, [$config]);

        $actions = $actionsGenerator->generate();

        $models = Yii::createObject(SchemaToDatabase::class, [$config])->prepareModels();

        $urlRulesGenerator = Yii::createObject(UrlRulesGenerator::class, [$config, $actions]);
        $files = $urlRulesGenerator->generate();

        $controllersGenerator = Yii::createObject(ControllersGenerator::class, [$config, $actions]);
        $files->merge($controllersGenerator->generate());

        $transformersGenerator = Yii::createObject(TransformersGenerator::class, [$config, $models]);
        $files->merge($transformersGenerator->generate());

        $modelsGenerator = Yii::createObject(ModelsGenerator::class, [$config, $models]);
        $files->merge($modelsGenerator->generate());

        $migrationsGenerator = Yii::createObject(MigrationsGenerator::class, [$config, $models, Yii::$app->db]);
        $files->merge($migrationsGenerator->generate());

        return $files->all();
    }

    /**
     * @return \cebe\openapi\spec\OpenApi
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    protected function getOpenApiWithoutReferences():OpenApi
    {
        if ($this->_openApiWithoutRef === null) {
            $file = Yii::getAlias($this->openApiPath);
            if (StringHelper::endsWith($this->openApiPath, '.json', false)) {
                $this->_openApiWithoutRef = Reader::readFromJsonFile($file, OpenApi::class, true);
            } else {
                $this->_openApiWithoutRef = Reader::readFromYamlFile($file, OpenApi::class, true);
            }
        }
        return $this->_openApiWithoutRef;
    }
}
