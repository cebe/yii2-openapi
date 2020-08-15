<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\generator;

use cebe\openapi\Reader;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use cebe\yii2openapi\lib\items\DbModel;
use cebe\yii2openapi\lib\MigrationsGenerator;
use cebe\yii2openapi\lib\SchemaToDatabase;
use Exception;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Yii;
use yii\db\ColumnSchema;
use yii\di\Instance;
use yii\gii\CodeFile;
use yii\gii\Generator;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use function array_filter;
use const YII_ENV_TEST;

/**
 *
 *
 */
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
     * @var array List of model names to exclude.
     */
    public $excludeModels = [];
    /**
     * @var array Generate database models only for Schemas that have the `x-table` annotation.
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
            [['openApiPath', 'urlConfigFile', 'controllerNamespace', 'modelNamespace', 'fakerNamespace', 'migrationPath', 'migrationNamespace'], 'filter', 'filter' => 'trim'],

            [['controllerNamespace', 'migrationNamespace'], 'default', 'value' => null],

            [['ignoreSpecErrors', 'generateUrls', 'generateModels', 'generateModelFaker', 'generateControllers', 'generateModelsOnlyXTable'], 'boolean'],

            ['openApiPath', 'required'],
            ['openApiPath', 'validateSpec'],

            [['urlConfigFile'], 'required', 'when' => function (ApiGenerator $model) {
                return (bool) $model->generateUrls;
            }],
            [['modelNamespace'], 'required', 'when' => function (ApiGenerator $model) {
                return (bool) $model->generateModels;
            }],
            [['fakerNamespace'], 'required', 'when' => function (ApiGenerator $model) {
                return (bool) $model->generateModelFaker;
            }],
            [['migrationPath'], 'required', 'when' => function (ApiGenerator $model) {
                return (bool) $model->generateMigrations;
            }],

        ]);
    }

    public function validateSpec($attribute)
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
        return array_merge(parent::attributeLabels(), [
            'openApiPath' => 'OpenAPI 3 Spec file',
            'generateUrls' => 'Generate URL Rules',
            'generateModelsOnlyXTable' => 'Generate DB Models and Tables only for schemas that include `x-table` property',
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
            'controllerNamespace' => 'Namespace to create controllers in. This must be resolvable via Yii alias. Default is the application controller namespace: <code>Yii::$app->controllerNamespace</code>.',
            'modelNamespace' => 'Namespace to create models in. This must be resolvable via Yii alias.',
            'fakerNamespace' => 'Namespace to create fake data generators in. This must be resolvable via Yii alias.',
            'migrationPath' => 'Path to create migration files in.',
            'migrationNamespace' => 'Namespace to create migrations in. If this is empty, migrations are generated without namespace.',
            'generateModelFaker' => 'Generate Faker for generating dummy data for each model.',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function autoCompleteData()
    {
        $vendor = Yii::getAlias('@vendor');
        $app = Yii::getAlias('@app');
        $runtime = Yii::getAlias('@runtime');
        $paths = [];
        $pathIterator = new RecursiveDirectoryIterator($app);
        $recursiveIterator = new RecursiveIteratorIterator($pathIterator);
        $files = new RegexIterator($recursiveIterator, '~.+\.(json|yaml|yml)$~i', RegexIterator::GET_MATCH);
        foreach ($files as $file) {
            if (strpos($file[0], $vendor) === 0) {
                $file = '@vendor' . substr($file[0], strlen($vendor));
                if (DIRECTORY_SEPARATOR === '\\') {
                    $file = str_replace('\\', '/', $file);
                }
            } elseif (strpos($file[0], $runtime) === 0) {
                $file = null;
            } elseif (strpos($file[0], $app) === 0) {
                $file = '@app' . substr($file[0], strlen($app));
                if (DIRECTORY_SEPARATOR === '\\') {
                    $file = str_replace('\\', '/', $file);
                }
            } else {
                $file = $file[0];
            }

            if ($file !== null) {
                $paths[] = $file;
            }
        }

        $namespaces = array_map(function ($alias) {
            $path = Yii::getAlias($alias, false);
            if (in_array($alias, ['@web', '@runtime', '@vendor', '@bower', '@npm'])) {
                return [];
            }
            if (!file_exists($path)) {
                return [];
            }
            try {
                return array_map(function ($dir) use ($path, $alias) {
                    return str_replace('/', '\\', substr($alias, 1) . substr($dir, strlen($path)));
                }, FileHelper::findDirectories($path, ['except' => [
                    'vendor/',
                    'runtime/',
                    'assets/',
                    '.git/',
                    '.svn/',
                ]]));
            } catch (\Throwable $e) {
                // ignore errors with file permissions
                Yii::error($e);
                return [];
            }
        }, array_keys(Yii::$aliases));
        $namespaces = array_merge(...$namespaces);

        return [
            'openApiPath' => $paths,
            'controllerNamespace' => $namespaces,
            'modelNamespace' => $namespaces,
            'fakerNamespace' => $namespaces,
            'migrationNamespace' => $namespaces,
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
        if ($this->generateControllers) {
            $required[] = 'controller.php';
        }
        if ($this->generateModels) {
            $required[] = 'dbmodel.php';
        }
        if ($this->generateModelsOnlyXTable) {
            $required[] = 'model.php';
        }
        if ($this->generateModelFaker) {
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
        return array_merge(parent::stickyAttributes(), ['generateUrls', 'urlConfigFile', 'controllerNamespace', 'modelNamespace', 'fakerNamespace', 'migrationPath', 'migrationNamespace']);
    }



    /**
     * @var OpenApi
     */
    private $_openApi;
    /**
     * @var OpenApi
     */
    private $_openApiWithoutRef;


    /**
     * @return OpenApi
     */
    protected function getOpenApi()
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

    protected function getOpenApiWithoutReferences()
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


    protected function generateUrls()
    {
        $openApi = $this->getOpenApi();

        $urlRules = [];
        foreach ($openApi->paths as $path => $pathItem) {
            /** @var $pathItem PathItem */
            if ($path[0] !== '/') {
                throw new Exception('Path must begin with /');
            }
            if ($pathItem === null) {
                continue;
            }
            if ($pathItem instanceof Reference) {
                $pathItem = $pathItem->resolve();
            }

            $parts = explode('/', trim($path, '/'));

            $controller = [];
            $action = [];
            $params = false;
            $actionParams = [];
            $idParam = null;
            foreach ($parts as $p => $part) {
                if (preg_match('/\{(.*)\}/', $part, $m)) {
                    $params = true;
                    $parts[$p] = '<' . $m[1] . '>';
                    if (isset($pathItem->parameters[$m[1]])) {
                        $actionParams[$m[1]] = $pathItem->parameters[$m[1]];
                    } else {
                        $actionParams[$m[1]] = null;
                    }
                    if ($idParam === null && preg_match('/\bid\b/i', Inflector::camel2id($m[1]))) {
                        $idParam = $m[1];
                    }
                    // TODO add regex to param based on openAPI type
                } elseif ($params) {
                    $action[] = $part;
                } else {
                    $controller[] = Inflector::camel2id(Inflector::singularize($part));
                }
            }
            $pattern = implode('/', $parts);

            $controller = implode('-', $controller);
            if (empty($controller)) {
                $controller = 'default';
            }
            $action = empty($action) ? '' : '-' . implode('-', $action);
            foreach ($pathItem->getOperations() as $method => $operation) {
                switch ($method) {
                    case 'get': $a = $params ? 'view' : 'index'; break;
                    case 'post': $a = 'create'; break;
                    case 'put': $a = 'update'; break;
                    case 'patch': $a = 'update'; break;
                    case 'delete': $a = 'delete'; break;
                    default: $a = "http-$method"; break;
                }
                $modelClass = $this->guessModelClass($operation, $a);
                $responseWrapper = $this->findResponseWrapper($operation, $a, $modelClass);
                // fallback to known model class on same URL
                if ($modelClass === null && isset($this->_knownModelclasses[$path])) {
                    $modelClass = $this->_knownModelclasses[$path];
                } else {
                    $this->_knownModelclasses[$path] = $modelClass;
                }
                $urlRules[] = [
                    'path' => $path,
                    'method' => strtoupper($method),
                    'pattern' => $pattern,
                    'route' => "$controller/$a$action",
                    'actionParams' => $actionParams,
                    'idParam' => $idParam,
                    'openApiOperation' => $operation,
                    'modelClass' => $modelClass !== null ? $this->modelNamespace . '\\' . $modelClass : null,
                    'responseWrapper' => $responseWrapper,
                ];
            }
            // TODO add options action
        }
        return $urlRules;
    }

    private $_knownModelclasses = [];

    private function guessModelClass(Operation $operation, $actionName)
    {
        switch ($actionName) {
            case 'create':
            case 'update':
            case 'delete':

                // first, check request body

                $requestBody = $operation->requestBody;
                if ($requestBody !== null) {
                    if ($requestBody instanceof Reference) {
                        $requestBody = $requestBody->resolve();
                    }
                    foreach ($requestBody->content as $contentType => $content) {
                        [$modelClass, ] = $this->guessModelClassFromContent($content);
                        if ($modelClass !== null) {
                            return $modelClass;
                        }
                    }
                }

                // no break, check response body if guess did not find model in request body
            case 'view':
            case 'index':

                // then, check response body

                if (!isset($operation->responses)) {
                    break;
                }
                foreach ($operation->responses as $code => $successResponse) {
                    if (((string) $code)[0] !== '2') {
                        continue;
                    }
                    if ($successResponse instanceof Reference) {
                        $successResponse = $successResponse->resolve();
                    }
                    foreach ($successResponse->content as $contentType => $content) {
                        [$modelClass, ] = $this->guessModelClassFromContent($content);
                        if ($modelClass !== null) {
                            return $modelClass;
                        }
                    }
                }

                break;
        }
    }

    private function guessModelClassFromContent(MediaType $content)
    {
        /** @var $referencedSchema Schema */
        if ($content->schema instanceof Reference) {
            $referencedSchema = $content->schema->resolve();
            // Model data is directly returned
            if ($referencedSchema->type === null || $referencedSchema->type === 'object') {
                $ref = $content->schema->getJsonReference()->getJsonPointer()->getPointer();
                if (strpos($ref, '/components/schemas/') === 0) {
                    return [substr($ref, 20), '', ''];
                }
            }
            // an array of Model data is directly returned
            if ($referencedSchema->type === 'array' && $referencedSchema->items instanceof Reference) {
                $ref = $referencedSchema->items->getJsonReference()->getJsonPointer()->getPointer();
                if (strpos($ref, '/components/schemas/') === 0) {
                    return [substr($ref, 20), '', ''];
                }
            }
        } else {
            $referencedSchema = $content->schema;
        }
        if ($referencedSchema === null) {
            return [null, null, null];
        }
        if ($referencedSchema->type === null || $referencedSchema->type === 'object') {
            foreach ($referencedSchema->properties as $propertyName => $property) {
                if ($property instanceof Reference) {
                    $referencedModelSchema = $property->resolve();
                    if ($referencedModelSchema->type === null || $referencedModelSchema->type === 'object') {
                        // Model data is wrapped
                        $ref = $property->getJsonReference()->getJsonPointer()->getPointer();
                        if (strpos($ref, '/components/schemas/') === 0) {
                            return [substr($ref, 20), $propertyName, null];
                        }
                    } elseif ($referencedModelSchema->type === 'array' && $referencedModelSchema->items instanceof Reference) {
                        // an array of Model data is wrapped
                        $ref = $referencedModelSchema->items->getJsonReference()->getJsonPointer()->getPointer();
                        if (strpos($ref, '/components/schemas/') === 0) {
                            return [substr($ref, 20), null, $propertyName];
                        }
                    }
                } elseif ($property->type === 'array' && $property->items instanceof Reference) {
                    // an array of Model data is wrapped
                    $ref = $property->items->getJsonReference()->getJsonPointer()->getPointer();
                    if (strpos($ref, '/components/schemas/') === 0) {
                        return [substr($ref, 20), null, $propertyName];
                    }
                }
            }
        }
        if ($referencedSchema->type === 'array' && $referencedSchema->items instanceof Reference) {
            $ref = $referencedSchema->items->getJsonReference()->getJsonPointer()->getPointer();
            if (strpos($ref, '/components/schemas/') === 0) {
                return [substr($ref, 20), '', ''];
            }
        }
        return [null, null, null];
    }

    /**
     * Figure out whether response item is wrapped in response.
     * @param Operation $operation
     * @param $actionName
     * @param $modelClass
     * @return null|array
     */
    private function findResponseWrapper(Operation $operation, $actionName, $modelClass)
    {
        if (!isset($operation->responses)) {
            return null;
        }
        foreach ($operation->responses as $code => $successResponse) {
            if (((string) $code)[0] !== '2') {
                continue;
            }
            if ($successResponse instanceof Reference) {
                $successResponse = $successResponse->resolve();
            }
            foreach ($successResponse->content as $contentType => $content) {
                [$detectedModelClass, $itemWrapper, $itemsWrapper] = $this->guessModelClassFromContent($content);
                if (($itemWrapper !== null || $itemsWrapper !== null) && $detectedModelClass === $modelClass) {
                    return [$itemWrapper, $itemsWrapper];
                }
            }
        }
        return null;
    }


    /**
     * @param Reference $reference
     * @return \cebe\openapi\SpecObjectInterface
     */
    private function resolveReference(Reference $reference)
    {
        return $reference->resolve(new ReferenceContext($this->getOpenApi(), Yii::getAlias($this->openApiPath)));
    }

    protected function generateControllers()
    {
        $urls = $this->generateUrls();

        $c = [];
        foreach ($urls as $url) {
            $parts = explode('/', $url['route'], 2);
            $c[$parts[0]][] = [
                'id' => $parts[1],
                'params' => array_keys($url['actionParams']),
                'idParam' => $url['idParam'] ?? null,
                'modelClass' => $url['modelClass'],
                'responseWrapper' => $url['responseWrapper'],
            ];
        }
        return $c;
    }

    /**
     * @return DbModel[]|array
    */
    protected function generateModels()
    {
        $converter = Yii::createObject([
            'class'=>SchemaToDatabase::class,
            'excludeModels' => $this->excludeModels,
            'generateModelsOnlyXTable' => $this->generateModelsOnlyXTable,
        ]);
        return $converter->generateModels($this->getOpenApi());
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
            $urls = [];
            $optionsUrls = [];
            foreach ($this->generateUrls() as $urlRule) {
                $urls["{$urlRule['method']} {$urlRule['pattern']}"] = $urlRule['route'];
                // add options action
                $parts = explode('/', $urlRule['route']);
                unset($parts[count($parts) - 1]);
                $optionsUrls[$urlRule['pattern']] = implode('/', $parts) . '/options';
            }
            $urls = array_merge($urls, $optionsUrls);
            $files[] = new CodeFile(
                Yii::getAlias($this->urlConfigFile),
                $this->render('urls.php', [
                    'urls' => $urls,
                ])
            );
        }

        if ($this->generateControllers) {
            $controllers = $this->generateControllers();
            $controllerNamespace = $this->controllerNamespace ?? Yii::$app->controllerNamespace;
            $controllerPath = $this->getPathFromNamespace($controllerNamespace);
            foreach ($controllers as $controller => $actions) {
                $className = \yii\helpers\Inflector::id2camel($controller) . 'Controller';
                $files[] = new CodeFile(
                    Yii::getAlias($controllerPath . "/base/$className.php"),
                    $this->render('controller.php', [
                        'className' => $className,
                        'namespace' => $controllerNamespace . '\\base',
                        'actions' => $actions,
                    ])
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
                    $classFileGenerator->setClasses([$reflection]);
                    $files[] = new CodeFile(
                        Yii::getAlias("$controllerPath/$className.php"),
                        $classFileGenerator->generate()
                    );
                }
            }
        }

        if ($this->generateModels) {
            $models = $this->generateModels();
            $modelPath = $this->getPathFromNamespace($this->modelNamespace);
            foreach ($models as $modelName => $model) {
                $className = $model->getClassName();
                if ($model instanceof DbModel) {
                    $files[] = new CodeFile(
                        Yii::getAlias("$modelPath/base/$className.php"),
                        $this->render('dbmodel.php', [
                            'model' => $model,
                            'namespace' => $this->modelNamespace . '\\base',
                            'relationNamespace' => $this->modelNamespace,
                        ])
                    );
                    if ($this->generateModelFaker) {
                        $fakerPath = $this->getPathFromNamespace($this->fakerNamespace);
                        $files[] = new CodeFile(
                            Yii::getAlias("$fakerPath/{$className}Faker.php"),
                            $this->render('faker.php', [
                                'model' => $model,
                                'modelNamespace' => $this->modelNamespace,
                                'namespace' => $this->fakerNamespace
                            ])
                        );
                    }
                } else {
                    /** This case not implemented yet, just keep it **/
                    $files[] = new CodeFile(
                        Yii::getAlias("$modelPath/base/$className.php"),
                        $this->render('model.php', [
                            'className' => $className,
                            'namespace' => $this->modelNamespace,
                            'description' => $model['description'],
                            'attributes' => $model['attributes'],
                        ])
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
        }

        if ($this->generateMigrations) {
            if (!isset($models)) {
                $models = $this->generateModels();
            }
            /** @var $migrationGenerator MigrationsGenerator */
            $migrationGenerator = Instance::ensure($this->migrationGenerator, MigrationsGenerator::class);
            $migrationModels = $migrationGenerator->generate(array_filter($models, function ($model) {
                return $model instanceof DbModel;
            }));

            $migrationPath = Yii::getAlias($this->migrationPath);
            $migrationNamespace = $this->migrationNamespace;
            $isTransactional = Yii::$app->db->getDriverName() === 'pgsql';//Probably some another yet

            // TODO start $i by looking at all files, otherwise only one generation per hours causes correct order!!!

            $i = 0;
            foreach ($migrationModels as $tableName => $migration) {
                // migration files get invalidated directly after generating,
                // if they contain a timestamp use fixed time here instead
                do {
                    $date = YII_ENV_TEST ? '200000_00': '';
                    $className = $migration->makeClassNameByTime($i, $migrationNamespace, $date);
                    $i++;
                } while (file_exists(Yii::getAlias("$migrationPath/$className.php")));


                $files[] = new CodeFile(
                    Yii::getAlias("$migrationPath/$className.php"),
                    $this->render('migration.php', [
                        'isTransactional' => $isTransactional,
                        'namespace' => $migrationNamespace,
                        'migration' => $migration
                    ])
                );
            }
        }

        return $files;
    }

    private function getPathFromNamespace($namespace)
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $namespace));
    }
}
