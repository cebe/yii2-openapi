<?php

return [
    'openApiPath' => '@specs/issue_fix/quote_in_alter_table/pgsql/quote_in_alter_table.yaml',
    'generateUrls' => false,
    'generateModels' => true,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => false,
    'generateMigrations' => true,
    'generateModelFaker' => true,
];
