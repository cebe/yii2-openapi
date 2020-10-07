<?php

return [
    'openApiPath' => '@specs/petstore_arrayref.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'useJsonApi' => true,
    'generateMigrations' => false,
];
