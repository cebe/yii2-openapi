<?php

/**
 * @copyright Copyright (c) 2018 Carsten Brandt <mail@cebe.cc> and contributors
 * @license https://github.com/cebe/yii2-openapi/blob/master/LICENSE
 */

namespace cebe\yii2openapi\lib;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\StringHelper;

class Config extends BaseObject
{
    /**
     * @var string path to the OpenAPI specification file. This can be an absolute path or a Yii path alias.
     */
    public $openApiPath;

    /**
     * @var bool whether to generate URL rules for Yii UrlManager from the API spec.
     */
    public $generateUrls = true;

    /**
     * @var string file name for URL rules.
     */
    public $urlConfigFile = '@app/config/urls.rest.php';

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
     **/
    public $urlPrefixes = [];

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
     * @var bool if true "Id" suffixes for foreignKeys and junction tables will be generated in camelCase like userId
     * postId, by default snake case used - user_id,post_id
     */
    public $camelCaseColumnNames = false;

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

    private $fileRenderer;

    /**
     * @var OpenApi
     */
    private $openApi;

    /**
     * @return \cebe\openapi\spec\OpenApi
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    public function getOpenApi():OpenApi
    {
        if ($this->openApi === null) {
            $file = Yii::getAlias($this->openApiPath);
            if (StringHelper::endsWith($this->openApiPath, '.json', false)) {
                $this->openApi = Reader::readFromJsonFile($file, OpenApi::class, false);
            } else {
                $this->openApi = Reader::readFromYamlFile($file, OpenApi::class, false);
            }
        }
        return $this->openApi;
    }

    public function getPathFromNamespace(string $namespace):string
    {
        return Yii::getAlias('@' . str_replace('\\', '/', ltrim($namespace, '\\')));
    }

    /**
     * @param \Closure $renderCallback
     */
    public function setFileRenderer($renderCallback):void
    {
        $this->fileRenderer = $renderCallback;
    }

    /**
     * Generates code using the specified code template and parameters.
     * Note that the code template will be used as a PHP file.
     * @param string $template the code template file. This must be specified as a file path
     * relative to [[templatePath]].
     * @param array $params list of parameters to be passed to the template file.
     * @return string the generated code
     */
    public function render($template, $params = [])
    {
        if (!$this->fileRenderer) {
            throw new InvalidConfigException('Renderer is not configured');
        }
        return \call_user_func($this->fileRenderer, $template, $params);
    }
}
