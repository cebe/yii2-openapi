<?php

return [
    'openApiPath' => '@specs/issue_fix/172_schemayaml_requestbody_has_no_effect/index.yaml',
    'generateUrls' => false,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'generateModelFaker' => false, // `generateModels` must be `true` in orde to use `generateModelFaker` as `true`
];

