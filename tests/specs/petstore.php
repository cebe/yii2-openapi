<?php

return [
    'openApiPath' => '@specs/petstore.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false, // TODO add tests for migrations
];
