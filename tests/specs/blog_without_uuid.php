<?php
return [
    'openApiPath' => '@specs/blog_without_uuid.yaml',
    'generateUrls' => false,
    'generateControllers' => false,
    'generateModels' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];
