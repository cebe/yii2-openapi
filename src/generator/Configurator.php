<?php

namespace cebe\yii2openapi\generator;

use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use Yii;
use yii\base\BaseObject;
use yii\helpers\StringHelper;

class Configurator extends BaseObject
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

    /**
     * @var OpenApi
     */
    private $openApi;

    /**
     * @var OpenApi
     */
    private $openApiWithoutRef;

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

    /**
     * @return \cebe\openapi\spec\OpenApi
     * @throws \cebe\openapi\exceptions\IOException
     * @throws \cebe\openapi\exceptions\TypeErrorException
     * @throws \cebe\openapi\exceptions\UnresolvableReferenceException
     */
    public function getOpenApiWithoutReferences():OpenApi
    {
        if ($this->openApiWithoutRef === null) {
            $file = Yii::getAlias($this->openApiPath);
            if (StringHelper::endsWith($this->openApiPath, '.json', false)) {
                $this->openApiWithoutRef = Reader::readFromJsonFile($file, OpenApi::class, true);
            } else {
                $this->openApiWithoutRef = Reader::readFromYamlFile($file, OpenApi::class, true);
            }
        }
        return $this->openApiWithoutRef;
    }
}
