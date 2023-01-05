<?php

return [
    'openApiPath' => '@specs/id_not_in_rules/id_not_in_rules.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true,
];
