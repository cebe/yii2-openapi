<?php

return [
    'openApiPath' => '@specs/change_column_name/pgsql/change_column_name.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
