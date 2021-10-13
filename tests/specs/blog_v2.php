<?php
return [
    'openApiPath' => '@specs/blog_v2.yaml',
    'generateUrls' => true,
    'generateControllers' => true,
    'generateModels' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];