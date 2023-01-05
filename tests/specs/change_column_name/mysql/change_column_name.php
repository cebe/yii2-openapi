<?php

return [
    'openApiPath' => '@specs/change_column_name/mysql/change_column_name.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
