<?php
return [
    'openApiPath' => '@specs/blog_jsonapi.yaml',
    'generateUrls' => true,
    'generateControllers' => true,
    'useJsonApi' => true,
    'extendableTransformers' => true,
    'generateModels' => false,
    'generateMigrations' => false,
    'excludeModels' => [
        'Error',
    ],
    'controllerModelMap' => [
        'Category' => 'Group',
        'Comment' => 'Reply'
    ]
];
