<?php
return [
    'openApiPath' => '@specs/many2many.yaml',
    'generateUrls' => false,
    'generateControllers' => true,
    'generateModels' => true,
    'useJsonApi' => true,
    'extendableTransformers' => false,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];
