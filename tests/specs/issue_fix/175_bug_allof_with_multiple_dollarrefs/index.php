<?php

return [
    'openApiPath' => '@specs/issue_fix/175_bug_allof_with_multiple_dollarrefs/index.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in orde to use `generateModelFaker` as `true`
];

