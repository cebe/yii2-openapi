<?php

return [
    'openApiPath' => '@specs/issue_fix/158_bug_giiapi_generated_rules_enum_with_trim/index.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => false,
    'generateModelFaker' => true, // `generateModels` must be `true` in orde to use `generateModelFaker` as `true`
];

