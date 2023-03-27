<?php

return [
    'openApiPath' => '@specs/fk_col_name/fk_col_name.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
