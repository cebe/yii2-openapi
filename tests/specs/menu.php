<?php
return [
    'openApiPath' => '@specs/menu.yaml',
    'generateUrls' => false,
    'generateControllers' => false,
    'generateModels' => true,
    'generateMigrations' => true,
    'excludeModels' => [
        'Error',
    ],
];