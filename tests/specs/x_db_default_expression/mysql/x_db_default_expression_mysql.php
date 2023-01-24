<?php

return [
    'openApiPath' => '@specs/x_db_default_expression/mysql/x_db_default_expression_mysql.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
