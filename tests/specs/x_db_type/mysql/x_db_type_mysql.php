<?php

return [
    'openApiPath' => '@specs/x_db_type/mysql/x_db_type_mysql.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
