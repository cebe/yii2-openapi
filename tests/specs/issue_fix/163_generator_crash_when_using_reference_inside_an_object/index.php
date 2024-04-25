<?php

return [
    'openApiPath' => '@specs/issue_fix/162_bug_dollarref_with_x_faker/162_bug_dollarref_with_x_faker.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in orde to use `generateModelFaker` as `true`
];

