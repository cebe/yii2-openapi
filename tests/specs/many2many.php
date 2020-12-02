<?php
return [
    'openApiPath' => '@specs/many2many.yaml',
    'generateUrls' => false,
    'generateControllers' => true,
    'generateModels' => true,
    'useJsonApi' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];
