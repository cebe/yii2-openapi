<?php

return [
    'openApiPath' => '@specs/petstore_urlprefixes.yaml',
    'generateUrls' => true,
    'generateModels' => false,
    'excludeModels' => [
        'Error',
    ],
    'generateControllers' => true,
    'generateMigrations' => false,
    'urlPrefixes' => [
        'animals' => '',
        '/info' => ['module' =>'petinfo','namespace' => '\app\modules\petinfo\controllers'],
        '/api/v1' => ['path' => '@app/modules/api/v1/controllers', 'namespace' => '\app\api\v1\controllers']
    ]
];
