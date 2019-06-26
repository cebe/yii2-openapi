<?php

return [
    'openApiPath' => '@specs/petstore_arrayref.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false, // TODO add tests for migrations
];
