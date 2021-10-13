<?php

// same as petstore, but with custom namespaces

return [
    'openApiPath' => '@specs/petstore.yaml',
    'generateUrls' => true,
    'urlConfigFile' => '@app/config/rest-urls.php',
    'generateModels' => true,
    'modelNamespace' => 'app\\mymodels',
    'fakerNamespace' => 'app\\mymodels\\faker',
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'controllerNamespace' => 'app\\mycontrollers',
    'generateMigrations' => false, // TODO add tests for migrations
];
