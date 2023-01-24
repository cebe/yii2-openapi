<?php

return [
    'openApiPath' => '@specs/issue_fix/create_table_in_down_code/create_table_in_down_code.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
