<?php

return [
    'openApiPath' => '@specs/issue_fix/camel_case_127/camel_case_127.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => false,
];
