<?php
return [
    'openApiPath' => '@specs/many2many.yaml',
    'generateUrls' => false,
    'generateControllers' => false,
    'generateModels' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];
