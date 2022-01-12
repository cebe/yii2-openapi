<?php

return [
    'openApiPath' => '@specs/ref_noobject.yaml',
    'generateUrls' => true,
    'generateControllers' => true,
    'useJsonApi' => true,
    'extendableTransformers' => false,
    'generateModels' => true,
    'generateModelsOnlyXTable' => false,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];
