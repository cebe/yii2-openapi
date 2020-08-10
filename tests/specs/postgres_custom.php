<?php
return [
    'openApiPath' => '@specs/postgres_custom.yaml',
    'generateUrls' => false,
    'generateControllers' => false,
    'generateModels' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];