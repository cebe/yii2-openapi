<?php
return [
    'openApiPath' => '@specs/blog_jsonapi.yaml',
    'generateUrls' => true,
    'generateControllers' => true,
    'useJsonApi' => true,
    'generateModels' => false,
    'generateMigrations' => false,
    'excludeModels' => [
        'Error',
    ],
];
