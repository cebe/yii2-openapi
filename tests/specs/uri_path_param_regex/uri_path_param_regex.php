<?php

return [
    'openApiPath' => '@specs/uri_path_param_regex/uri_path_param_regex.yaml',
    'generateUrls' => true,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => false,
];
