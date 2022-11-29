<?php

return [
    'openApiPath' => '@specs/x_db_type/pgsql/petstore_x_db_type.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
