<?php

return [
    'openApiPath' => '@specs/x_db_type/pgsql/x_db_type_pgsql.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
