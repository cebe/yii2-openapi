<?php

return [
    'openApiPath' => '@specs/x_db_type/petstore_x_db_type_v2.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
