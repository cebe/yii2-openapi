<?php

return [
    'openApiPath' => '@specs/issue_fix/163_generator_crash_when_using_reference_inside_an_object/index.yaml',
    'generateUrls' => true,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => true,
    'generateModelFaker' => true, // `generateModels` must be `true` in orde to use `generateModelFaker` as `true`
];

