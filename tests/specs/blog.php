<?php
return [
    'openApiPath' => '@specs/blog.yaml',
    'generateUrls' => false,
    'generateControllers' => false,
    'generateModels' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];