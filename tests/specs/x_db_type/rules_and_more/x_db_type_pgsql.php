<?php

return [
    'openApiPath' => '@specs/x_db_type/fresh/pgsql/x_db_type_pgsql.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true,
    'fakerNamespace' => 'app\\models\\pgsqlfaker',
    'modelNamespace' => 'app\\models\\pgsqlmodel',
];
