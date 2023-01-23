<?php

return [
    'openApiPath' => '@specs/x_db_default_expression/pgsql/x_db_default_expression_pgsql.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
