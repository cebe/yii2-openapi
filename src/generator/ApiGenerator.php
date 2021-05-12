<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\generator;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\yii2openapi\lib\FractalGenerator;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\items\FractalAction;
use cebe\yii2openapi\lib\MigrationsGenerator;
use cebe\yii2openapi\lib\PathAutoCompletion;
use cebe\yii2openapi\lib\SchemaToDatabase;
use cebe\yii2openapi\lib\TransformerGenerator;
use cebe\yii2openapi\lib\UrlGenerator;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Yii;
use yii\di\Instance;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use const YII_ENV_TEST;

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
     * @var bool if true, transformers will be generate in base subdirectory and overridable classes will extend it
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

    public $migrationGenerator = MigrationsGenerator::class;

    /**
     * @var OpenApi
     */
    private $_openApi;

    /**
     * @var OpenApi
     */
    private $_openApiWithoutRef;

    /**
     * @var DbModel[]
    **/
    private $preparedModels;

    /**
     * @var \cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction
    **/
    private $preparedActions;

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
                    'filter' => 'trim',
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
                        'singularResourceKeys'
                    ],
                    'boolean',
                ],

                ['openApiPath', 'required'],
                ['openApiPath', 'validateSpec'],

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
                        return (bool)$model->generateControllers && (bool) $model->useJsonApi;
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

        $openApi = $this->getOpenApiWithoutReferences();
        if (!$openApi->validate()) {
            $this->addError($attribute, 'Failed to validate OpenAPI spec:' . Html::ul($openApi->getErrors()));
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
                'skipUnderscoredSchemas'=>'Generate DB Models and Tables only for schemas that not starts with underscore'
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
                'singularResourceKeys' => 'Use singular resource keys (/post/{id}) (Plural by defaut : /posts/{id})',
                'transformerNamespace' => 'Namespace to create fractal transformers',
                'extendableTransformers' => 'If checked, transformers will be generate in base subdirectory and overridable classes will extend it, otherwise it will be autogenerated only'
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
        return array_merge(
            $this->generateUrls(),
            $this->generateControllers(),
            $this->generateModels(),
            $this->generateMigrations()
        );
    }

    /**
     * @return array|\yii\gii\CodeFile[]
     * @throws \Exception
     */
    protected function generateUrls(): array
    {
        if (!$this->generateUrls) {
            return [];
        }
        $urls = [];
        $optionsUrls = [];
        foreach ($this->prepareActions() as $action) {
            $urls["{$action->requestMethod} {$action->urlPattern}"] = $action->route;
            //@TODO: need to ensure
            $optionsUrls[$action->urlPattern] = $action->getOptionsRoute();
        }
        $urls = array_merge($urls, $optionsUrls);
        $file = new CodeFile(Yii::getAlias($this->urlConfigFile), $this->render('urls.php', ['urls' => $urls]));
        return [$file];
    }

    /**
     * @return array|CodeFile[]
     * @throws \Exception
     */
    protected function generateControllers(): array
    {
        $files = [];
        if (! $this->generateControllers) {
            return $files;
        }
        $controllers = $this->prepareControllers();
        $controllerNamespace = $this->controllerNamespace ?? Yii::$app->controllerNamespace;
        $controllerPath = $this->getPathFromNamespace($controllerNamespace);
        $templateName = $this->useJsonApi? 'controller_jsonapi.php': 'controller.php';

        foreach ($controllers as $controller => $actions) {
            $className = Inflector::id2camel($controller) . 'Controller';
            $files[] = new CodeFile(
                Yii::getAlias($controllerPath . "/base/$className.php"),
                $this->render(
                    $templateName,
                    [
                        'className' => $className,
                        'namespace' => $controllerNamespace . '\\base',
                        'actions' => $actions,
                    ]
                )
            );
            // only generate custom classes if they do not exist, do not override
            if (!file_exists(Yii::getAlias("$controllerPath/$className.php"))) {
                $classFileGenerator = new FileGenerator();
                $reflection = new ClassGenerator(
                    $className,
                    $controllerNamespace,
                    null,
                    $controllerNamespace . '\\base\\' . $className
                );

                if ($this->useJsonApi) {
                    $body = <<<'PHP'
$actions = parent::actions();
return $actions;
PHP;
                    $reflection->addMethod('actions', [], MethodGenerator::FLAG_PUBLIC, $body);
                }
                $classFileGenerator->setClasses([$reflection]);
                $files[] = new CodeFile(
                    Yii::getAlias("$controllerPath/$className.php"),
                    $classFileGenerator->generate()
                );
            }
        }
        if ($this->useJsonApi) {
            $transformers = $this->prepareTransformers();
            $transformerPath = $this->getPathFromNamespace($this->transformerNamespace);
            foreach ($transformers as $transformer) {
                $dirPath = $transformerPath . ($this->extendableTransformers ? '/base' : '');
                $files[] = new CodeFile(
                    Yii::getAlias("{$dirPath}/{$transformer->name}.php"),
                    $this->render('transformer.php', [
                        'namespace' => $this->transformerNamespace . ($this->extendableTransformers ? '\\base' : ''),
                        'mainNamespace' => $this->transformerNamespace,
                        'extendable' => $this->extendableTransformers,
                        'transformer' => $transformer
                    ])
                );

                if (!$this->extendableTransformers) {
                    continue;
                }
                // only generate custom classes if they do not exist, do not override
                if (!file_exists(Yii::getAlias("$transformerPath/{$transformer->name}.php"))) {
                    $classFileGenerator = new FileGenerator();
                    $reflection = new ClassGenerator(
                        $transformer->name,
                        $this->transformerNamespace,
                        null,
                        $this->transformerNamespace . '\\base\\' . $transformer->name
                    );
                    $classFileGenerator->setClasses([$reflection]);
                    $files[] = new CodeFile(
                        Yii::getAlias("$transformerPath/{$transformer->name}.php"),
                        $classFileGenerator->generate()
                    );
                }
            }
        }
        return $files;
    }

    /**
     * @return array|CodeFile[]
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     * @throws \yii\base\InvalidConfigException
     */
    protected function generateModels(): array
    {
        $files = [];
        if (!$this->generateModels) {
            return $files;
        }
        $models = $this->prepareModels();
        $modelPath = $this->getPathFromNamespace($this->modelNamespace);
        foreach ($models as $modelName => $model) {
            $className = $model->getClassName();
            if ($model instanceof DbModel) {
                $files[] = new CodeFile(
                    Yii::getAlias("$modelPath/base/$className.php"),
                    $this->render(
                        'dbmodel.php',
                        [
                            'model' => $model,
                            'namespace' => $this->modelNamespace . '\\base',
                            'relationNamespace' => $this->modelNamespace,
                        ]
                    )
                );
                if ($this->generateModelFaker) {
                    $fakerPath = $this->getPathFromNamespace($this->fakerNamespace);
                    $files[] = new CodeFile(
                        Yii::getAlias("$fakerPath/BaseModelFaker.php"),
                        $this->render('basefaker.php', ['namespace' => $this->fakerNamespace])
                    );
                    $files[] = new CodeFile(
                        Yii::getAlias("$fakerPath/{$className}Faker.php"),
                        $this->render(
                            'faker.php',
                            [
                                'model' => $model,
                                'modelNamespace' => $this->modelNamespace,
                                'namespace' => $this->fakerNamespace,
                            ]
                        )
                    );
                }
            } else {
                /** This case not implemented yet, just keep it **/
                $files[] = new CodeFile(
                    Yii::getAlias("$modelPath/base/$className.php"),
                    $this->render(
                        'model.php',
                        [
                            'className' => $className,
                            'namespace' => $this->modelNamespace,
                            'description' => $model['description'],
                            'attributes' => $model['attributes'],
                        ]
                    )
                );
            }

            // only generate custom classes if they do not exist, do not override
            if (!file_exists(Yii::getAlias("$modelPath/$className.php"))) {
                $classFileGenerator = new FileGenerator();
                $reflection = new ClassGenerator(
                    $className,
                    $this->modelNamespace,
                    null,
                    $this->modelNamespace . '\\base\\' . $className
                );
                $classFileGenerator->setClasses([$reflection]);
                $files[] = new CodeFile(
                    Yii::getAlias("$modelPath/$className.php"),
                    $classFileGenerator->generate()
                );
            }
        }
        return $files;
    }

    /**
     * @return array|CodeFile[]
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    protected function generateMigrations(): array
    {
        $files = [];
        if (!$this->generateMigrations) {
            return $files;
        }
        $models = $this->prepareModels();
        /** @var $migrationGenerator MigrationsGenerator */
        $migrationGenerator = Instance::ensure($this->migrationGenerator, MigrationsGenerator::class);
        $migrationModels = $migrationGenerator->generate(
            array_filter($models, function ($model) {
                return $model instanceof DbModel;
            })
        );
        $migrationPath = Yii::getAlias($this->migrationPath);
        $migrationNamespace = $this->migrationNamespace;
        $isTransactional = Yii::$app->db->getDriverName() === 'pgsql';//Probably some another yet

        // TODO start $i by looking at all files, otherwise only one generation per hours causes correct order!!!

        $i = 0;
        foreach ($migrationModels as $tableName => $migration) {
            // migration files get invalidated directly after generating,
            // if they contain a timestamp use fixed time here instead
            do {
                $date = YII_ENV_TEST ? '200000_00' : '';
                $className = $migration->makeClassNameByTime($i, $migrationNamespace, $date);
                $i++;
            } while (file_exists(Yii::getAlias("$migrationPath/$className.php")));

            $files[] = new CodeFile(
                Yii::getAlias("$migrationPath/$className.php"),
                $this->render(
                    'migration.php',
                    [
                        'isTransactional' => $isTransactional,
                        'namespace' => $migrationNamespace,
                        'migration' => $migration,
                    ]
                )
            );
        }
        return $files;
    }

    /**
     * @return \cebe\openapi\spec\OpenApi
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    protected function getOpenApi():OpenApi
    {
        if ($this->_openApi === null) {
            $file = Yii::getAlias($this->openApiPath);
            if (StringHelper::endsWith($this->openApiPath, '.json', false)) {
                $this->_openApi = Reader::readFromJsonFile($file, OpenApi::class, false);
            } else {
                $this->_openApi = Reader::readFromYamlFile($file, OpenApi::class, false);
            }
        }
        return $this->_openApi;
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

    /**
     * @return array|\cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction
     * @throws \yii\base\InvalidConfigException
     * @throws \Exception
     */
    protected function prepareActions():array
    {
        if (!$this->preparedActions) {
            $generator = $this->useJsonApi
                ? new FractalGenerator(
                    $this->getOpenApi(),
                    $this->modelNamespace,
                    $this->controllerModelMap,
                    $this->transformerNamespace,
                    $this->singularResourceKeys
                )
                : new UrlGenerator($this->getOpenApi(), $this->modelNamespace, $this->controllerModelMap);
            $this->preparedActions = $generator->generate();
        }
        return $this->preparedActions;
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\RestAction[]|\cebe\yii2openapi\lib\items\FractalAction[]
     * @throws \Exception
     */
    protected function prepareControllers():array
    {
        $actions = $this->prepareActions();

        return ArrayHelper::index($actions, null, 'controllerId');
    }

    /**
     * @return DbModel[]|array
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     * @throws \yii\base\InvalidConfigException
     */
    protected function prepareModels():array
    {
        if (!$this->preparedModels) {
            $converter = Yii::createObject([
                'class' => SchemaToDatabase::class,
                'excludeModels' => $this->excludeModels,
                'skipUnderscoredSchemas' => $this->skipUnderscoredSchemas,
                'generateModelsOnlyXTable' => $this->generateModelsOnlyXTable,
            ]);
            $this->preparedModels = $converter->generateModels($this->getOpenApi());
        }
        return $this->preparedModels;
    }

    /**
     * @return array|\cebe\yii2openapi\lib\items\Transformer[]
     */
    protected function prepareTransformers(): array
    {
        $models = array_filter($this->prepareModels(), function ($model) {
            return $model instanceof DbModel;
        });
        $generator = new TransformerGenerator(
            $models,
            $this->transformerNamespace.($this->extendableTransformers? '\\base': ''),
            $this->modelNamespace,
            $this->singularResourceKeys
        );
        return $generator->generate();
    }

    /**
     * @param string $namespace
     * @return bool|string
     */
    private function getPathFromNamespace(string $namespace)
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $namespace));
    }
}
